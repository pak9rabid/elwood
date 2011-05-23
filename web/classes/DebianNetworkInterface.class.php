<?php
	require_once "NetworkInterface.class.php";
	require_once "FileUtils.class.php";
	require_once "Console.class.php";
		
	class DebianNetworkInterface extends NetworkInterface
	{
		const INTERFACES_FILE = "/etc/network/interfaces";
		
		// Override
		public function save()
		{
			parent::save();
			
			$out = implode("\n", array_merge($this->removeInterfaceFromConfig(), $this->generateConfig()));
			FileUtils::writeToFile(self::INTERFACES_FILE, $out);
		}
		
		// Override
		public function delete()
		{
			parent::delete();
			
			$out = implode("\n", $this->removeInterfaceFromConfig());
			FileUtils::writeToFile(self::INTERFACES_FILE, $out);
		}
		
		// Override
		public function apply()
		{
			$tempFile = "/tmp/" . uniqid();
			$config = $this->generateConfig();
			$interface = $this->getPhysicalInterface();
			
			FileUtils::writeToFile($tempFile, implode("\n", $config));
			Console::execute("sudo /sbin/ifdown -i $tempFile $interface", true);
			Console::execute("sudo /sbin/ifup -i $tempFile $interface");
			
			// bring up any aliases
			foreach ($this->getAliases() as $key => $alias)
				Console::execute("sudo /sbin/ifup -i $tempFile --force $interface:$key");
			
			// remove temp interfaces file
			unlink($tempFile);
		}
		
		private function generateConfig()
		{
			$out = array();
			
			$name = $this->getPhysicalInterface();
			$address = $this->getAddress();
			$aliases = $this->getAliases();
			$bridgedInts = $this->getBridgedInterfaces();
			$mtu = $this->getMtu();
			
			$out[] = "\nauto $name";
			
			if ($this->usesDhcp())
				$mode = "dhcp";
			else if (empty($address))
				$mode = "manual";
			else
				$mode = "static";
				
			$out[] = "iface $name inet $mode";
			
			if ($mode == "static")
			{
				list($ip, $netmask) = explode("/", $address);
				$netmask = NetUtils::cidr2Mask($netmask);
				$networkAndBroadcast = NetUtils::calculate($ip, $netmask);
				$gateway = $this->getGateway();
				
				$out[] = "address $ip";
				$out[] = "netmask $netmask";
				$out[] = "network " . $networkAndBroadcast->network;
				$out[] = "broadcast " . $networkAndBroadcast->broadcast;
				
				if (!empty($gateway))
					$out[] = "gateway $gateway";
			}
			
			if (!empty($mtu))
			{
				if ($mode == "static")
					$out[] = "mtu $mtu";
				else
					$out[] = "post-up /sbin/ifconfig $name";
			}
			
			// removes any aliased addresses for this interface
			$out[] = "post-down ip addr flush dev $name";
			
			if (!empty($bridgedInts))
			{
				$out[] = "bridge_ports " . implode(" ", $bridgedInts);
				$out[] = "bridge_maxwait 0";
			}
			
			// add any aliases
			foreach ($aliases as $key => $alias)
			{
				$aliasName = "$name:$key";
				list($ip, $netmask) = explode("/", $alias);
				$netmask = NetUtils::cidr2Mask($netmask);
				$networkAndBroadcast = NetUtils::calculate($ip, $netmask);
				
				$out[] = "\nauto $aliasName";
				$out[] = "iface $aliasName inet static";
				$out[] = "address $ip";
				$out[] = "netmask $netmask";
				$out[] = "network " . $networkAndBroadcast->network;
				$out[] = "broadcast " . $networkAndBroadcast->broadcast;
			}
			
			return $out;
		}
		
		private function filterCommentBlank($line)
		{
			$line = trim($line);
			return !empty($line) && !preg_match("/^#.*$/", $line);
		}
		
		private function readInterfacesFile()
		{			
			// remove commented and blank lines			
			return array_filter(FileUtils::readFileAsArray(self::INTERFACES_FILE), array(&$this, "filterCommentBlank"));
		}
		
		private function removeInterfaceFromConfig()
		{
			$interface = $this->getPhysicalInterface();
			$content = $this->readInterfacesFile();
			$remove = false;
						
			foreach ($content as $key => $line)
			{
				$line = trim($line);
				
				if (preg_match("/^auto ($interface$|$interface:[0-9]{1,}$)/", $line)) // matches this interface or any aliases under it
					$remove = true;
				else if (preg_match("/^auto/", $line)) // some other interface
				{	
					$remove = false;
					$content[$key] = "\n$line";
				}
				
				if ($remove)
					unset($content[$key]);
			}
						
			return $content;
		}
	}
?>