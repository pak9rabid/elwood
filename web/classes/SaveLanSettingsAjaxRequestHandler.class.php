<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "NetworkInterface.class.php";
	require_once "Service.class.php";
	require_once "User.class.php";
	
	class SaveLanSettingsAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to change LAN settings"));
			
			$lanInt = NetworkInterface::getInstance("LAN");
			$dhcpService = Service::getInstance("dhcp");
			$dhcpService->load();
			$dhcpService->clearAccessRules();
			
			$errors = array();
			
			if (!isset($parameters['ipRanges']))
				$errors[] = "At least one IP range must be specified";
			
			foreach ($parameters as $key => $value)
			{
				try
				{
					switch ($key)
					{
						case "ipAddress":
							$ip = trim($value);
							
							if (empty($ip))
								$errors[] = "IP address not specified";
							else
								$lanInt->setAddress($ip);
								
							break;				
						case "mtu":
							$mtu = trim($value);
							
							if (empty($mtu))
								$lanInt->setMtu(1500);
							else
								$lanInt->setMtu($mtu);
								
							break;
						case "domain":
							$domain = trim($value);
							
							if (empty($domain))
								$errors[] = "Domain not specified";
							else
								$dhcpService->setDomain($domain);
								
							break;
						case "nameservers":
							$dhcpService->setNameservers($value);
							break;
						case "ipRanges":
							$dhcpService->setIpRanges($value);
							break;	
						case "stickyIps":
							$dhcpService->setStickyIps($value);
							break;
					}
					
					if (!isset($parameters['nameservers']))
						$dhcpService->setNameservers(array());
												
					if (!isset($parameters['stickyIps']))
						$dhcpService->setStickyIps(array());
				}
				catch (Exception $ex)
				{
					$errors[] = $ex->getMessage();
				}
			}
			
			if (!empty($errors))
				return new AjaxResponse("", $errors);
			
			$lanInt->setUsesDhcp(false);
			$lanInt->save();
			$lanInt->apply();
			
			if ($parameters['isDhcpServerEnabled'] == "true")
			{
				$dhcpService->setAttribute("is_enabled", "Y");
				$dhcpService->save();
				$dhcpService->restart();
				$dhcpService->setAccessRules($dhcpService->getDefaultAccessRules());
			}
			else
			{
				$dhcpService->setAttribute("is_enabled", "N");
				$dhcpService->save();
				$dhcpService->stop();
			}
			
			$dhcpService->applyAccessRules();
			
			return new AjaxResponse("LAN settings saved successfully");
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>