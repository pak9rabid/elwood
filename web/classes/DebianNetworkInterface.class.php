<?php
	require_once "NetworkInterface.class.php";
	require_once "RouterSettings.class.php";
	require_once "Console.class.php";
	require_once "NetUtils.class.php";
	require_once "FileUtils.class.php";
	
	abstract class DebianNetworkInterface extends NetworkInterface
	{
		// Override
		public function save()
		{
			$out =	"auto " . $this->name . "\n" .
					"iface " . $this->name . "inet " . ($this->usesDhcp ? "dhcp" : "static") . "\n";
			
			if (!$this->usesDhcp)
			{
				$networkAndBroadcast = NetUtils::calculate($this->ip, $this->netmask);
				
				$out .=	"address " . $this->ip . "\n" .
						"netmask " . $this->netmask . "\n" .
						"network " . $networkAndBroadcast['network'] . "\n" .
						"broadcast " . $networkAndBroadcast['broadcast'] . "\n" .
						(!empty($this->gateway) ? "gateway " . $this->gateway : "");
			}

		
		}
		
		// Override
		public function load()
		{
			$ifName = $this->name;
			
			$content = FileUtils::readFileAsArray("/etc/network/interfaces");
			
			// remove commented and blank lines
			function filterCommentBlank($line)
			{
				$line = trim($line);
				return !empty($line) && !preg_match("/^#.*$/", $line);
			}
			
			$content = array_filter($content, "filterCommentBlank");
					
			// uses dhcp?			
			foreach ($content as $line)
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
		}
		
		// Override
		public function apply()
		{
			
		}
	}
?>