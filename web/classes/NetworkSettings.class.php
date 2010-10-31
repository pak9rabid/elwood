<?php
	require_once "FileUtils.class.php";
	require_once "NetworkInterface.class.php";
	
	class NetworkSettings
	{
		private $wanInterface;
		private $lanInterface;
		private $nameservers;
		
		public function __construct()
		{
			$this->wanInterface = NetworkInterface::getInstance("wan");
			$this->lanInterface = NetworkInterface::getInstance("lan");
			
			$this->nameservers = array();
			$content = FileUtils::readFileAsArray("/etc/resolv.conf");
			
			foreach ($content as $line)
			{
				if (preg_match("/^nameserver.*$/", $line))
				{
					list($temp, $nameserver) = explode(" ", $line);
					$this->nameservers[] = $nameserver;
				}
			}
		}
		
		public function getWanInterface()
		{
			return $this->wanInterface;
		}
		
		public function getLanInterface()
		{
			return $this->lanInterface;
		}
		
		public function getNameservers()
		{
			return $this->nameservers;
		}
	}
?>