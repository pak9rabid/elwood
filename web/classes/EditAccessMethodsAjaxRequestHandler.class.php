<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallChain.class.php";
	require_once "Service.class.php";
	require_once "HTTPService.class.php";
	require_once "SSHService.class.php";
	require_once "User.class.php";
	
	class EditAccessMethodsAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to change access methods"));
				return;
			}
			
			$httpWan = (boolean)$parameters['httpWan'];
			$httpLan = (boolean)$parameters['httpLan'];
			$sshWan = (boolean)$parameters['sshWan'];
			$sshLan = (boolean)$parameters['sshLan'];
			$icmpWan = (boolean)$parameters['icmpWan'];
			$icmpLan = (boolean)$parameters['icmpLan'];
			$httpPort = $parameters['httpPort'];
			$sshPort = $parameters['sshPort'];
			
			$extIf = RouterSettings::getSettingValue("EXTIF");
			$intIf = RouterSettings::getSettingValue("INTIF");
			
			// Validate
			$errors = array();
			
			if (($httpWan || $httpLan) && !NetUtils::isValidIanaPortNumber($httpPort))
				$errors[] = "Invalid HTTP port number specified";
				
			if (($sshWan || $sshLan) && !NetUtils::isValidIanaPortNumber($sshPort))
				$errors[] = "Invalid SSH port number specified";
				
			if (!empty($errors))
			{
				$this->response = new AjaxResponse("", $errors);
				
				return;
			}
			
			$httpService = Service::getInstance("http");
			$sshService = Service::getInstance("ssh");
			
			if (!($httpService instanceof HTTPService))
				throw new Exception("HTTP service class does not implement the HTTPService interface");
				
			if (!($sshService instanceof SSHService))
				throw new Exception("SSH service class does not implement the SSHService interface");
				
			$httpService->load();
			$sshService->load();
			$httpService->clearAccessRules();
			$sshService->clearAccessRules();
			
			$inputChain = new FirewallChain("filter", "INPUT");
			$rule = new FirewallRule();
			
			// allow any ESTABLISHED or RELATED traffic
			$rule->setAttribute("state", "ESTABLISH,RELATED");
			$rule->setAttribute("target", "ACCEPT");
			$inputChain->add($rule);
						
			$rule->clear();
			$rule->setAttribute("protocol", "tcp");
			$rule->setAttribute("target", "ACCEPT");
			
			// http		
			if ($httpWan || $httpLan)
			{
				$httpService->setAttribute("is_enabled", "Y");
				$rule->setAttribute("dport", $httpPort);
				$rule->setAttribute("service_id", $httpService->getAttribute("id"));
				
				if ($httpWan && !$httpLan)
					$rule->setAttribute("int_in", $extIf);
				else if ($httpLan && !$httpWan)
					$rule->setAttribute("int_in", $intIf);

				$httpService->addAccessRule($rule);
			}
			else
				$httpService->setAttribute("is_enabled", "N");
			
			$rule->clear();
			$rule->setAttribute("protocol", "tcp");
			$rule->setAttribute("target", "ACCEPT");
			
			// ssh
			if ($sshWan || $sshLan)
			{
				$sshService->setAttribute("is_enabled", "Y");
				$rule->setAttribute("dport", $sshPort);
				$rule->setAttribute("service_id", $sshService->getAttribute("id"));
				
				if ($sshWan && !$sshLan)
					$rule->setAttribute("int_in", $extIf);
				else if ($sshLan && !$sshWan)
					$rule->setAttribute("int_in", $intIf);
					
				$sshService->addAccessRule($rule);
			}
			else
				$sshService->setAttribute("is_enabled", "N");
				
				
			$rule->clear();
			$rule->setAttribute("protocol", "icmp");
			$rule->setAttribute("target", "ACCEPT");
				
			// icmp
			if ($icmpWan || $icmpLan)
			{
				if ($icmpWan && !$icmpLan)
				{
					$rule->setAttribute("int_in", $extIf);
					RouterSettings::saveSetting("LAN_ICMP_ENABLED", 0);
					RouterSettings::saveSetting("WAN_ICMP_ENABLED", 1);
				}
				else if ($icmpLan && !$icmpWan)
				{
					$rule->setAttribute("int_in", $intIf);
					RouterSettings::saveSetting("LAN_ICMP_ENABLED", 1);
					RouterSettings::saveSetting("WAN_ICMP_ENABLED", 0);
				}
				else
				{
					RouterSettings::saveSetting("LAN_ICMP_ENABLED", 1);
					RouterSettings::saveSetting("WAN_ICMP_ENABLED", 1);
				}
					
				$inputChain->add($rule);
			}
			else
			{
				RouterSettings::saveSetting("LAN_ICMP_ENABLED", 0);
				RouterSettings::saveSetting("WAN_ICMP_ENABLED", 0);
			}
						
			$inputChain->addRulesForService($httpService);
			$inputChain->addRulesForService($sshService);
			
			// load rules for other active services
			foreach (Service::getRegisteredServices() as $service)
			{
				$serviceName = $service->getAttribute("service_name");
				
				if ($serviceName == "http" || $serviceName == "ssh")
					continue;
					
				$service->load();
				
				if ($service->getAttribute("is_enabled") == "Y")
					$inputChain->addRulesForService($service);
			}
			
			$inputChain->save();
			$inputChain->apply();
			
			$httpService->save();
			$sshService->save();
											
			if ($sshService->getAttribute("is_enabled") == "Y")
				$sshService->restart();
			else
				$sshService->stop();
				
			if ($httpService->getAttribute("is_enabled") == "Y")
				$httpService->restart();
			else
				$httpService->stop();
			
			// Finished, set success response
			$this->response = new AjaxResponse();
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>