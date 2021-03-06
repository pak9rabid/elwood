<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "NetworkInterface.class.php";
	require_once "Service.class.php";
	require_once "HTTPService.class.php";
	require_once "SSHService.class.php";
	require_once "User.class.php";
	
	class EditAccessMethodsAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to change access methods"));
			
			$httpWan = (boolean)$parameters['httpWan'];
			$httpLan = (boolean)$parameters['httpLan'];
			$sshWan = (boolean)$parameters['sshWan'];
			$sshLan = (boolean)$parameters['sshLan'];
			$icmpWan = (boolean)$parameters['icmpWan'];
			$icmpLan = (boolean)$parameters['icmpLan'];
			$httpPort = $parameters['httpPort'];
			$sshPort = $parameters['sshPort'];
			
			$wanIf = NetworkInterface::getInstance("WAN")->getPhysicalInterface();
			$lanIf = NetworkInterface::getInstance("LAN")->getPhysicalInterface();
			
			// Validate
			$errors = array();
			
			if (($httpWan || $httpLan) && !NetUtils::isValidIanaPortNumber($httpPort))
				$errors[] = "Invalid HTTP port number specified";
				
			if (($sshWan || $sshLan) && !NetUtils::isValidIanaPortNumber($sshPort))
				$errors[] = "Invalid SSH port number specified";
				
			if (!empty($errors))
				return new AjaxResponse("", $errors);
			
			$httpService = Service::getInstance("http");
			$sshService = Service::getInstance("ssh");
			$icmpService = Service::getInstance("icmp");
			
			if (!($httpService instanceof HTTPService))
				throw new Exception("HTTP service class does not implement the HTTPService interface");
				
			if (!($sshService instanceof SSHService))
				throw new Exception("SSH service class does not implement the SSHService interface");
				
			$httpService->load();
			$sshService->load();
			$icmpService->load();
			
			$rule = new FirewallRule();
			$rule->setAttribute("protocol", "tcp");
			$rule->setAttribute("target", "ACCEPT");
			
			// http
			if ($httpWan || $httpLan)
			{
				$httpService->setAttribute("is_enabled", "Y");
				$rule->setAttribute("dport", $httpPort);
				$rule->setAttribute("service_id", $httpService->getAttribute("id"));
				
				if ($httpWan && !$httpLan)
					$rule->setAttribute("int_in", $wanIf);
				else if ($httpLan && !$httpWan)
					$rule->setAttribute("int_in", $lanIf);

				$httpService->setAccessRules(array($rule));
			}
			else
			{
				$httpService->clearAccessRules();
				$httpService->setAttribute("is_enabled", "N");
			}
			
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
					$rule->setAttribute("int_in", $wanIf);
				else if ($sshLan && !$sshWan)
					$rule->setAttribute("int_in", $lanIf);
					
				$sshService->setAccessRules(array($rule));
			}
			else
			{
				$sshService->clearAccessRules();
				$sshService->setAttribute("is_enabled", "N");
			}
			
			$rule->clear();
			$rule->setAttribute("protocol", "icmp");
			$rule->setAttribute("target", "ACCEPT");
			
			// icmp
			if ($icmpWan || $icmpLan)
			{
				$icmpService->setAttribute("is_enabled", "Y");
				$rule->setAttribute("service_id", $icmpService->getAttribute("id"));
				
				if ($icmpWan && !$icmpLan)
					$rule->setAttribute("int_in", $wanIf);
				else if ($icmpLan && !$icmpWan)
					$rule->setAttribute("int_in", $lanIf);
					
				$icmpService->setAccessRules(array($rule));
			}
			else
			{
				$icmpService->clearAccessRules();
				$icmpService->setAttribute("is_enabled", "N");
			}
			
			$httpService->save();
			$sshService->save();
			$icmpService->save();
			$httpService->applyAccessRules();
			$sshService->applyAccessRules();
			$icmpService->applyAccessRules();
			
			if ($sshService->getAttribute("is_enabled") == "Y")
				$sshService->restart();
			else
				$sshService->stop();
				
			if ($httpService->getAttribute("is_enabled") == "Y")
				$httpService->restart();
			else
				$httpService->stop();
			
			// Finished, set success response
			return new AjaxResponse();
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>