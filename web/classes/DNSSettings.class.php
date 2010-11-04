<?php
	require_once "FileUtils.class.php";
	require_once "NetUtils.class.php";
	
	class DNSSettings
	{
		private $nameservers;
		
		public function __construct()
		{
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
		
		public function getNameservers()
		{
			return $this->nameservers;
		}
		
		public function setNameservers(array $nameservers)
		{
			foreach ($nameservers as $nameserver)
			{
				if (!NetUtils::isValidIp($nameserver))
					throw new Exception("Invalid nameserver specified");
			}
			
			$this->nameservers = $nameservers;
		}
		
		public function save()
		{
			$out = array();
			
			foreach ($this->nameservers as $nameserver)
				$out[] = "nameserver $nameserver";
				
			FileUtils::writeToFile("/etc/resolv.conf", implode("\n", $out));
		}
	}
?>