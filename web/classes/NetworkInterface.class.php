<?php
	require_once "SystemProfile.class.php";
	require_once "NetUtils.class.php";
	require_once "NetworkInterfaceAlias.class.php";
	
	abstract class NetworkInterface
	{
		protected $name;
		protected $usesDhcp;
		protected $ip;
		protected $netmask;
		protected $mtu;
		protected $gateway;
		protected $aliases = array();
		
		abstract public function save();
		abstract public function load();
		abstract public function apply();
		
		public static function getInstance($interfaceName)
		{
			$profile = SystemProfile::getProfile();
			$interface = $profile->interfaces->$interfaceName;
			
			if (empty($interface))
				throw new Exception("Interface $interfaceName does not exist in profile " . $profile->name);
				
			$className = $interface->class;
			
			if (empty($className))
				throw new Exception("Class $className does not exist in interface $interfaceName in profile " . $profile->name);
				
			require_once "$className.class.php";
			
			$interface = new $className();
			
			if (!$interface instanceof self)
				throw new Exception("$className is not a subclass of NetworkInterface");
			
			$interface->load();
			return $interface;
		}
		
		public function getName()
		{
			return $this->name;
		}
		
		public function usesDhcp()
		{
			return $this->usesDhcp;
		}
		
		public function getIp()
		{
			return $this->ip;
		}
		
		public function getNetmask()
		{
			return $this->netmask;
		}
		
		public function getMtu()
		{
			return $this->mtu;
		}
		
		public function getGateway()
		{
			return $this->gateway;
		}
				
		public function setName($name)
		{
			$this->name = $name;
		}
		
		public function setUsesDhcp($usesDhcp)
		{
			$this->usesDhcp = (boolean) $usesDhcp;
		}
		
		public function setIp($ip)
		{
			if (empty($ip) || NetUtils::isValidIp($ip))
				$this->ip = $ip;
			else
				throw new Exception("Invalid IP address specified");
		}
		
		public function setNetmask($netmask)
		{
			// $netmask can be in either regular notation (xxx.xxx.xxx.xxx), or CIDR notation (xx)
			if ($netmask != 0 && empty($netmask))
				$this->netmask = $netmask;
			else
			{
				if (NetUtils::isValidNetmask($netmask))
					$this->netmask = $netmask;
				else if (NetUtils::isValidCidr($netmask))
					$this->netmask = NetUtils::cidr2Mask($netmask);
				else
					throw new Exception("Invalid subnet mask specified");
			}
		}
		
		public function setMtu($mtu)
		{
			if (empty($mtu) || NetUtils::isValidMtu($mtu))
				$this->mtu = $mtu;
			else
				throw new Exception("Invalid MTU specified");
		}
		
		public function setGateway($gateway)
		{
			if (empty($gateway) || NetUtils::isValidIp($gateway))
				$this->gateway = $gateway;
			else
				throw new Exception("Invalid gateway specified");
		}
		
		public function getAliases()
		{
			return $this->aliases;
		}
		
		public function addAlias($ip, $netmask)
		{
			// ensure an alias with $ip and $netmask doesn't already exist
			foreach ($this->aliases as $alias)
			{
				if ($alias->getIp() == $ip && $alias->getNetmask() == $netmask)
					throw new Exception("Alias for interface " . $this->name . " with IP $ip and subnet mask $netmask already exists");
			}
			
			$this->aliases[] = new NetworkInterfaceAlias($this, $ip, $netmask);
		}
		
		public function removeAlias($ip, $netmask)
		{
			foreach ($this->aliases as $key => $alias)
			{
				if ($alias->getIp() == $ip && $alias->getNetmask() == $netmask)
				{
					unset($this->aliases[$key]);
					
					// should be the only alias with that ip/netmask..no need to continue checking the rest
					break;
				}
			}
			
			// re-key array...this ensures that the first alias starts with index 0 and that
			// there aren't any "holes" in the alias indexes after an alias has been removed
			$this->aliases = array_values($this->aliases);
		}
		
		public function clearAliases()
		{
			$this->aliases = array();
		}
	}
?>