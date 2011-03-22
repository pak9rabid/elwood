<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	require_once "User.class.php";
	
	class SaveWanSettingsAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to change WAN settings"));
				return;
			}
			
			$ipType = trim($parameters['ipType']);
			$dnsType = trim($parameters['dnsType']);
			$nameservers = $parameters['nameservers'];
			$mtu = trim($parameters['mtu']);
			
			$nameservers = empty($nameservers) ? array() : $nameservers; 
			$wanInt = NetworkInterface::getInstance("wan");
			$dns = new DNSSettings();
			$errors = array();
						
			// ip settings
			if ($ipType == "dhcp")
				$wanInt->setUsesDhcp(true);
			else if ($ipType == "static")
			{
				$wanInt->setUsesDhcp(false);
				
				foreach ($parameters as $key => $value)
				{
					try
					{
						switch ($key)
						{
							case "ipAddress":
								$ip = trim($value);
								
								if (empty($ip))
									throw new Exception("Invalid IP address specified");
									
								$wanInt->setIp($ip);
								break;
							case "netmask":
								$netmask = trim($value);
								
								if (empty($netmask))
									throw new Exception("Invalid subnet mask specified");
									
								$wanInt->setNetmask($netmask);
								break;
							case "gateway":
								$wanInt->setGateway(trim($value));
								break;
						}
					}
					catch (Exception $ex)
					{
						$errors[] = $ex->getMessage();
					}
				}
			}
			else
				$errors[] = "Invalid IP type specified";
				
			// dns settings
			if ($dnsType == "static")
			{
				try
				{
					$dns->setNameservers($nameservers);
				}
				catch (Exception $ex)
				{
					$errors[] = $ex->getMessage();
				}
			}
			
			// interface settings
			try
			{
				if (empty($mtu))
					throw new Exception("Invalid MTU specified");
					
				$wanInt->setMtu($mtu);
			}
			catch (Exception $ex)
			{
				$errors[] = $ex->getMessage();
			}
			
			if (empty($errors))
			{
				$wanInt->save();
				$wanInt->apply();
				
				if ($dnsType == "static")
					$dns->save();
				
				$this->response = new AjaxResponse("WAN settings saved successfully");
			}
			else
				$this->response = new AjaxResponse("", $errors);
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>