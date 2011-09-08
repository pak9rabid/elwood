<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "Service.class.php";
	require_once "User.class.php";
	
	class SaveWlanSettingsAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to make changes to wireless settings"));
			
			$wlanService = Service::getInstance("wlan");
			$wlanService->load();
			$errors = array();
			
			foreach ($parameters as $key => $value)
			{
				if (is_string($value))
					$value = trim($value);
								
				try
				{
					switch ($key)
					{
						case "ssid":
							$wlanService->setSsid($value);
							break;
						case "hideSsid":
							$wlanService->setHideSsid($value == "true");
							break;
						case "mode":
							$wlanService->setMode($value);
							break;
						case "channel":
							$wlanService->setChannel($value);
							break;
						case "securityMode":
							$wlanService->setSecurityMethod($value);
							break;
						case "wepKeys":
							if ($parameters['securityMode'] == WirelessSecurity::WEP)
								$wlanService->setWepKeys($value);
								
							break;
						case "wepKeyIndex":
							if ($parameters['securityMode'] == WirelessSecurity::WEP)
								$wlanService->setDefaultWepKeyIndex($value);
								
							break;
						case "wepAuthMode":
							if ($parameters['securityMode'] == WirelessSecurity::WEP)
								$wlanService->setAuthMethod($value);
								
							break;
						case "wpaPsk":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_PSK, WirelessSecurity::WPA2_PSK)))
								$wlanService->setWpaPassphrase($value);
								
							break;
						case "wpaAuthServerAddr":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAuthServerAddr($value);
								
							break;
						case "wpaAuthServerPort":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAuthServerPort($value);
								
							break;
						case "wpaAuthServerSec":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAuthServerSharedSecret($value);
								
							break;
						case "wpaAcctServerAddr":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAcctServerAddr($value);
								
							break;
						case "wpaAcctServerPort":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAcctServerPort($value);
								
							break;
						case "wpaAcctServerSec":
							if (in_array($parameters['securityMode'], array(WirelessSecurity::WPA_EAP, WirelessSecurity::WPA2_EAP)))
								$wlanService->setAcctServerSharedSecret($value);
								
							break;
					}
				}
				catch (Exception $ex)
				{
					$errors[] = $ex->getMessage();
				}
			}
			
			if (!empty($errors))
				return new AjaxResponse("", $errors);
						
			if ($parameters['isEnabled'] == "true")
			{
				$wlanService->setAttribute("is_enabled", "Y");
				$wlanService->save();
				$wlanService->restart();
			}
			else
			{
				$wlanService->setAttribute("is_enabled", "N");
				$wlanService->save();
				$wlanService->stop();
			}
				
			return new AjaxResponse("Wireless settings saved successfully");
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>