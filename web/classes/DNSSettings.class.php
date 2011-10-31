<?php
	require_once "FileUtils.class.php";
	require_once "NetUtils.class.php";
	require_once "RouterSettings.class.php";
	
	class DNSSettings
	{		
		public static function getNameservers()
		{
			$dnsServers = RouterSettings::getSettingValue("DNS_SERVERS");
			
			return (empty($dnsServers) ? array() : explode(",", $dnsServers));
		}
		
		public static function getSearchDomains()
		{
			try
			{
				$searchDomains = RouterSettings::getSettingValue("DNS_SEARCH_DOMAINS");
				return (empty($searchDomains) ? array() : explode(",", $searchDomains));
			}
			catch (SettingNotFoundException $ex)
			{
				return array();
			}
		}
		
		public static function setNameservers(array $nameservers)
		{
			foreach ($nameservers as $nameserver)
			{
				if (!NetUtils::isValidIp($nameserver))
					throw new Exception("Invalid nameserver specified");
			}
				
			RouterSettings::saveSetting("DNS_SERVERS", implode(",", $nameservers));
		}
		
		public static function setSearchDomains(array $domains)
		{
			$nsSetting = RouterSettings::getSetting("DNS_SEARCH_DOMAINS");
			$nsSetting->setAttribute("value", implode(",", $domains));
			$nsSetting->executeUpdate();
		}
		
		public static function apply()
		{
			$out = array();
			
			$searchDomains = self::getSearchDomains();
			
			if (!empty($searchDomains))
				$out[] = "search " . implode(" ", $searchDomains);
				
			foreach (self::getNameservers() as $nameserver)
				$out[] = "nameserver $nameserver";
				
			FileUtils::writeToFile("/etc/resolv.conf", implode("\n", $out));
		}
	}
?>