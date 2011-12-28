<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "SystemProfile.class.php";
	require_once "NetUtils.class.php";
	require_once "NetworkInterface.class.php";
	require_once "RouterSettings.class.php";
	require_once "DNSSettings.class.php";
	require_once "User.class.php";
	require_once "Database.class.php";
	require_once "NetworkInterfaceAlreadyUsedException.class.php";
	
	class SaveSetupSettingsAjaxRequestHandler implements AjaxRequestHandler
	{
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users can make system changes"));
				
			$sysProfile = isset($parameters['sysProfile']) ? $parameters['sysProfile'] : null;
			$elwoodWebRoot = isset($parameters['elwoodWebRoot']) ? $parameters['elwoodWebRoot'] : null;
			$wanInterface = isset($parameters['wanInterface']) ? $parameters['wanInterface'] : null;
			$lanInterface = isset($parameters['lanInterface']) ? $parameters['lanInterface'] : null;
			$apInterface = isset($parameters['apInterface']) ? $parameters['apInterface'] : null;
			$wanIpType = isset($parameters['wanIpType']) ? $parameters['wanIpType'] : null;
			$wanAddress = isset($parameters['wanAddress']) ? $parameters['wanAddress'] : null;
			$wanGateway = isset($parameters['wanGateway']) ? $parameters['wanGateway'] : null;
			$lanAddress = isset($parameters['lanAddress']) ? $parameters['lanAddress'] : null;
			$dnsMode = isset($parameters['dnsMode']) ? $parameters['dnsMode'] : null;
			$nameservers = isset($parameters['nameservers']) ? $parameters['nameservers'] : array();
			$searchDomains = isset($parameters['searchDomains']) ? $parameters['searchDomains'] : array();
			
			// validate
			$errors = array();
			
			if (!SystemProfile::isValidProfile($sysProfile))
				$errors[] = "Invalid system profile specified";			
							
			if (empty($elwoodWebRoot))
				$errors[] = "Elwood web root direcotry not specified";
				
			if (!in_array($wanInterface, NetworkInterface::getAvailableInterfaces()))
				$errors[] = "Invalid WAN interface specified";
				
			if (!in_array($lanInterface, NetworkInterface::getAvailableInterfaces()))
				$errors[] = "Invalid LAN interface specified";
				
			if (!empty($apInterface) && !in_array($apInterface, NetworkInterface::getAvailableWirelessInterfaces()))
				$errors[] = "Invalid AP interface specified";
			
			$interfaces = array($wanInterface, $lanInterface);
			
			if (!empty($apInterface))
				$interfaces[] = $apInterface;
				
			if (count($interfaces) != count(array_unique($interfaces)))
				$errors[] = "An interface was used for more than one role";
				
			if (!in_array($wanIpType, array("auto", "static")))
				$errors[] = "Invalid or no WAN IP type specified";
				
			if ($wanIpType == "static" && !NetUtils::isValidAddress($wanAddress))
				$errors[] = "Invalid WAN IP address specified";
				
			if ($wanIpType == "static" && !NetUtils::isValidIp($wanGateway))
				$errors[] = "Invalid WAN gateway IP specified";
				
			if (!NetUtils::isValidAddress($lanAddress))
				$errors[] = "Invalid LAN IP address specified";
				
			if (!in_array($dnsMode, array("auto", "static")))
				$errors[] = "Invalid or no DNS mode specified";
				
			foreach ($nameservers as $nameserver)
			{
				$nameserver = trim($nameserver);
				
				if (!empty($nameserver) && !NetUtils::isValidIp($nameserver))
					$errors[] = "Invalid nameserver specified: " . $nameserver;
			}
				
			if (!empty($errors))
				return new AjaxResponse("", $errors);
			
			RouterSettings::saveSetting("SYSTEM_PROFILE", $sysProfile);
			RouterSettings::saveSetting("ELWOOD_WEBROOT", $elwoodWebRoot);
			
			$wanIf = NetworkInterface::getInstance("WAN");
			
			try
			{
				$wanIf->setPhysicalInterface($wanInterface);
			}
			catch (NetworkInterfaceAlreadyUsedException $ex)
			{
				// remove  the interface first, then try again
				$wanIf->delete();
				$wanIf->setPhysicalInterface($wanInterface);
			}
			
			if ($wanIpType == "auto")
				$wanIf->setUsesDhcp(true);
			else 
			{
				$wanIf->setUsesDhcp(false);
				$wanIf->setAddress($wanAddress);
				$wanIf->setGateway($wanGateway);
			}
				
			$wanIf->save();
			
			$lanIf = NetworkInterface::getInstance("LAN");
			
			try
			{
				$lanIf->setPhysicalInterface($lanInterface);
			}
			catch (NetworkInterfaceAlreadyUsedException $ex)
			{
				// remove the interface first, then try again
				$lanIf->delete();
				$lanIf->setPhysicalInterface($lanInterface);
			}
			
			$lanIf->setAddress($lanAddress);
			$lanIf->save();
			
			if (!empty($apInterface))
			{
				$apIf = NetworkInterface::getInstance("AP");
				
				try
				{
					$apIf->setPhysicalInterface($apInterface);
					$apIf->save();
				}
				catch (NetworkInterfaceAlreadyUsedException $ex)
				{
					// remove the interface first, then try again
					$apIf->delete();
					$apIf->setPhysicalInterface($apInterface);
					$apIf->save();
				}
			}
						
			// apply interface settings
			$wanIf->apply();
			$lanIf->apply();
			
			// apply DNS settings
			if ($dnsMode == "static")
			{
				RouterSettings::saveSetting("DNS_MODE", "static");
				DNSSettings::setNameservers($nameservers);
				DNSSettings::setSearchDomains($searchDomains);
				DNSSettings::apply();
			}
			else
			{
				RouterSettings::saveSetting("DNS_MODE", "auto");				
				DNSSettings::setNameservers(DNSSettings::getActiveNameservers());
				DNSSettings::setSearchDomains(DNSSettings::getActiveSearchDomains());
			}
						
			RouterSettings::saveSetting("IS_INITIALIZED", 1);
			
			return new AjaxResponse("Settings saved successfully");
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>