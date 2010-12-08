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
								$wanInt->setIp(trim($value));
								break;
							case "netmask":
								$wanInt->setNetmask(trim($value));
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