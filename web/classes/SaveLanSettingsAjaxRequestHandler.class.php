<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "NetworkInterface.class.php";
	require_once "Service.class.php";
	require_once "User.class.php";
	
	class SaveLanSettingsAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to change LAN settings"));
				return;
			}
			
			$lanInt = NetworkInterface::getInstance("lan");
			$dhcpService = Service::getInstance("dhcp");
			$lanInt->load();
			
			$errors = array();
			
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
								$lanInt->setIP($ip);
								
							break;
						case "netmask":
							$netmask = trim($value);
							
							if (empty($netmask))
								$errors[] = "Subnet mask not specified";
							else
								$lanInt->setNetmask($netmask);
								
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
				}
				catch (Exception $ex)
				{
					$errors[] = $ex->getMessage();
				}
			}
			
			if (!empty($errors))
			{
				$this->response = new AjaxResponse("", $errors);
				return;
			}
			
			$lanInt->setUsesDhcp(false);
			$lanInt->save();
			$lanInt->apply();
			
			$dhcpService->save();
			
			if ($parameters['isDhcpServerEnabled'] == "true")
				$dhcpService->restart();
			else
				$dhcpService->stop();
			
			$this->response = new AjaxResponse("LAN settings saved successfully");
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>