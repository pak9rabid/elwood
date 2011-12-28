<?php
	require_once "FileUtils.class.php";
	require_once "NetUtils.class.php";
	require_once "RouterSettings.class.php";
	require_once "SettingNotFoundException.class.php";
	
	class DNSSettings
	{
		public static function getNameservers()
		{
			try
			{
				$dnsServers = RouterSettings::getSettingValue("DNS_SERVERS");
				return (empty($dnsServers) ? array() : explode(",", $dnsServers));
			}
			catch (SettingNotFoundException $ex)
			{
				return array();
			}
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
		
		public static function getActiveNameservers()
		{
			$nameservers = array();
			
			foreach (FileUtils::readFileAsArray("/etc/resolv.conf") as $line)
			{
				list($option, $value) = explode(" ", $line);
				
				if ($option == "nameserver")
					$nameservers[] = $value;
			}
			
			return $nameservers;
		}
		
		public static function getActiveSearchDomains()
		{
			$searchDomains = array();
			
			foreach (FileUtils::readFileAsArray("/etc/resolv.conf") as $line)
			{
				list($option) = explode(" ", $line);
				
				if ($option == "search")
				{
					foreach (explode(" ", $line) as $value)
					{
						$value = trim($value);
						
						if ($value == "search")
							continue;
						
						if (!empty($value))
							$searchDomains[] = $value;
					}
				}
			}
			
			return $searchDomains;
		}
		
		public static function setNameservers(array $nameservers)
		{
			// remove any duplicate entries
			$nameservers = array_unique($nameservers);
			
			// remove any blank values
			$nameservers = array_filter($nameservers, function($nameserver)
			{
				return trim($nameserver) != "";
			});
			
			foreach ($nameservers as $nameserver)
			{
				if (!NetUtils::isValidIp($nameserver))
					throw new Exception("Invalid nameserver specified");
			}
			
			if (!empty($nameservers))
				RouterSettings::saveSetting("DNS_SERVERS", implode(",", $nameservers));
			else
			{
				try
				{
					RouterSettings::deleteSetting("DNS_SERVERS");
				}
				catch (SettingNotFoundException $ex)
				{
					// setting already doesn't exist...ignore
				}
			}
		}
		
		public static function setSearchDomains(array $domains)
		{
			// remove any duplicate entries
			$domains = array_unique($domains);
			
			// remove any blank values
			$domains = array_filter($domains, function($domain)
			{
				return trim($domain) != "";
			});
			
			if (!empty($domains))
				RouterSettings::saveSetting("DNS_SEARCH_DOMAINS", implode(",", $domains));
			else
			{
				try
				{
					RouterSettings::deleteSetting("DNS_SEARCH_DOMAINS");
				}
				catch (SettingNotFoundException $ex)
				{
					// setting already doesn't exist...ignore
				}
			}
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