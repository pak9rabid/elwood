<?php
	require_once "NetworkInterface.class.php";
	require_once "NetUtils.class.php";
	
	class NetworkInterfaceAlias
	{
		protected $physicalInterface;
		protected $ip;
		protected $netmask;
		
		public function __construct(NetworkInterface $physicalInterface, $ip = "0.0.0.0", $netmask = "0.0.0.0")
		{
			$this->physicalInterface = $physicalInterface;
			$this->setIp($ip);
			$this->setNetmask($netmask);
		}
		
		public function setIp($ip)
		{
			if (!NetUtils::isValidIp($ip))
				throw new Exception("Invalid IP address specified");
				
			if ($ip == $this->physicalInterface->getIp())
				throw new Exception("Alias IP cannot be the same as the interface's physical IP");
				
			$this->ip = $ip;
		}
		
		public function setNetmask($netmask)
		{
			if (!NetUtils::isValidNetmask($netmask))
				throw new Exception("Invalid subnet mask specified");
				
			$this->netmask = $netmask;
		}
		
		public function getPhysicalInterface()
		{
			return $this->physicalInterface;
		}
		
		public function getIp()
		{
			return $this->ip;
		}
		
		public function getNetmask()
		{
			return $this->netmask;
		}
	}
?>