<?php
	require_once "SystemProfile.class.php";
	
	abstract class NetworkInterface
	{
		protected $usesDhcp;
		protected $ip;
		protected $netmask;
		protected $gateway;
		protected $mtu;
		
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
			
			return new $className();
		}
		
		abstract public function save();
		abstract public function load();
		abstract public function apply();
	}
?>