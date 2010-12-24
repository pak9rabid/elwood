<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "Service.class.php";
	
	class SaveWlanSettingsAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			$wlanService = Service::getInstance("wlan");
			$errors = array();
			
			foreach ($parameters as $key => $value)
			{
				if (is_string($value))
					$value = trim($value);
				
				if (empty($value))
					continue;
				
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
							$wlanService->setWepKeys($value);
							break;
						case "wepKeyIndex":
							$wlanService->setDefaultWepKeyIndex($value);
							break;
						case "wepAuthMode":
							$wlanService->setAuthMethod($value);
							break;
						case "wpaPsk":
							$wlanService->setWpaPassphrase($value);
							break;
						case "wpaAuthServerAddr":
							$wlanService->setAuthServerAddr($value);
							break;
						case "wpaAuthServerPort":
							$wlanService->setAuthServerPort($value);
							break;
						case "wpaAuthServerSec":
							$wlanService->setAuthServerSharedSecret($value);
							break;
						case "wpaAcctServerAddr":
							$wlanService->setAcctServerAddr($value);
							break;
						case "wpaAcctServerPort":
							$wlanService->setAcctServerPort($value);
							break;
						case "wpaAcctServerSec":
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
			{
				$this->response = new AjaxResponse("", $errors);
				return;
			}
			
			$wlanService->save();
			
			if ($parameters['isEnabled'] == "true")
				$wlanService->restart();
			else
				$wlanService->stop();
				
			$this->response = new AjaxResponse("Wireless settings saved successfully");
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>