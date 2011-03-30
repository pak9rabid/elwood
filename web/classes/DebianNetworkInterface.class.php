<?php
	require_once "NetworkInterface.class.php";
	require_once "RouterSettings.class.php";
	require_once "Console.class.php";
	require_once "NetUtils.class.php";
	require_once "FileUtils.class.php";
	require_once "Service.class.php";
	require_once "NetworkInterfaceAlias.class.php";
	
	abstract class DebianNetworkInterface extends NetworkInterface
	{
		protected $config;
		
		protected function generateConfig()
		{
			$out = array();
			
			$out[] = "\n" . "auto " . $this->name;
			$out[] = "iface " . $this->name . " inet " . ($this->usesDhcp ? "dhcp" : "static");
			
			if (!$this->usesDhcp)
			{
				$networkAndBroadcast = NetUtils::calculate($this->ip, $this->netmask);
				
				$out[] = "address " . $this->ip;
				$out[] = "netmask " . $this->netmask;
				$out[] = "network " . $networkAndBroadcast->network;
				$out[] = "broadcast " . $networkAndBroadcast->broadcast;
									
				if (!empty($this->gateway))
					$out[] = "gateway " . $this->gateway;
			}
			
			if (!empty($this->mtu))
			{
				if ($this->usesDhcp)
					$out[] = "post-up /sbin/ifconfig " . $this->name . " mtu " . $this->mtu;
				else
					$out[] = "mtu " . $this->mtu;
			}
			
			// remove any aliased addresses for this interface
			$out[] = "post-down ip addr flush dev " . $this->name;
			
			// add any aliases
			foreach ($this->aliases as $key => $alias)
			{
				$aliasName = $this->name . ":$key";
				$networkAndBroadcast = NetUtils::calculate($alias->getIp(), $alias->getNetmask());
				
				$out[] = "\n" . "auto $aliasName";
				$out[] = "iface $aliasName inet static";
				$out[] = "address " . $alias->getIp();
				$out[] = "netmask " . $alias->getNetmask();
				$out[] = "network " . $networkAndBroadcast->network;
				$out[] = "broadcast " . $networkAndBroadcast->broadcast;
			}
			
			return $out;
		}
		
		// Override
		public function save()
		{
			$content = $this->readInterfacesFile();
			$remove = false;
			
			foreach ($content as $key => $line)
			{
				$line = trim($line);
				
				if (preg_match("/^auto " . $this->name . "/", $line))
					$remove = true;
				else if (preg_match("/^auto [^" . $this->name . "].*/", $line))
				{
					$remove = false;
					$content[$key] = "\n" . $line;
				}
					
				if ($remove)
					unset($content[$key]);
			}
			
			$out = implode("\n", array_merge($content, $this->generateConfig()));
			FileUtils::writeToFile("/etc/network/interfaces", $out);
		}
		
		// Override
		public function load()
		{			
			$ifName = $this->name;
								
			// uses dhcp?			
			foreach ($this->readInterfacesFile() as $line)
			{
				if (preg_match("/^iface $ifName.*$/", $line))
				{
					$line = explode(" ", $line);
					$this->usesDhcp = (trim($line[3]) == "dhcp");
					break;
				}
			}
			
			// ip
			foreach (Console::execute("/sbin/ifconfig $ifName | grep 'inet addr' | awk '{print $2}' | cut -f2 -d':'") as $line)
			{
				$this->ip = $line;
				break;
			}
			
			// netmask
			foreach (Console::execute("/sbin/ifconfig $ifName | grep 'inet addr' | awk '{print $4}' | cut -f2 -d':'") as $line)
			{
				$this->netmask = $line;
				break;
			}
			
			// mtu
			foreach (Console::execute("/sbin/ifconfig $ifName | grep MTU | awk '{print $5}' | cut -f2 -d':'") as $line)
			{
				$this->mtu = $line;
				break;
			}
			
			// gateway
			foreach (Console::execute("/sbin/route -n | grep $ifName | egrep '^0\.0\.0\.0.*UG' | awk '{print $2}'") as $line)
			{
				$this->gateway = $line;
				break;
			}
			
			// aliases
			$this->aliases = array();
			
			foreach (Console::execute("/sbin/ifconfig | grep 'Link encap' | awk '{print $1}' | grep $ifName:", true) as $aliasInterface)
			{
				$alias = new NetworkInterfaceAlias($this);
				
				foreach (Console::execute("/sbin/ifconfig $aliasInterface | grep 'inet addr' | awk '{print $2}' | cut -f2 -d':'") as $ip)
					$alias->setIp($ip);
					
				foreach (Console::execute("/sbin/ifconfig $aliasInterface | grep 'inet addr' | awk '{print $4}' | cut -f2 -d':'") as $netmask)
					$alias->setNetmask($netmask);
					
				$this->aliases[] = $alias;
			}
		}
		
		// Override
		public function apply()
		{
			$netService = Service::getInstance("network");
			$netService->restart();
		}
		
		private function filterCommentBlank($line)
		{
			$line = trim($line);
			return !empty($line) && !preg_match("/^#.*$/", $line);
		}
		
		private function readInterfacesFile()
		{			
			// remove commented and blank lines			
			return array_filter(FileUtils::readFileAsArray("/etc/network/interfaces"), array(&$this, "filterCommentBlank"));
		}
	}
?>