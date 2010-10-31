<?php
	require_once "SystemProfile.class.php";
	require_once "NetUtils.class.php";
	
	abstract class NetworkInterface
	{
		protected $name;
		protected $usesDhcp;
		protected $ip;
		protected $netmask;
		protected $mtu;
		protected $gateway;
		
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
			if (empty($netmask) || NetUtils::isValidNetmask($netmask))
				$this->netmask = $netmask;
			else
				throw new Exception("Invalid netmask specified");
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
	}
?>