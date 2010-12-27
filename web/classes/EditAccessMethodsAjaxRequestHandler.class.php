<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "Service.class.php";
	require_once "HTTPService.class.php";
	require_once "SSHService.class.php";
	require_once "IPTablesFwFilterTranslator.class.php";
	require_once "FileUtils.class.php";
	require_once "TempDatabase.class.php";
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
			
			if (!NetUtils::isValidIanaPortNumber($httpPort))
				$errors[] = "Invalid HTTP port number specified";
				
			if (!NetUtils::isValidIanaPortNumber($sshPort))
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
			
			// Create temp database and clear INPUT chain
			$tempDb = new TempDatabase();
			IPTablesFwFilterTranslator::setDbFromSystem($tempDb);
			
			$rule = new FirewallFilterRule();
			$rule->setConnection($tempDb);
			$rule->setAttribute("chain_name", "INPUT");
			$rule->executeDelete();
			
			// We'll allow all ESTABLISHED,RELATED connections
			$rule->setAllAttributes(array("chain_name" => "INPUT", "state" => "ESTABLISHED,RELATED", "target" => "ACCEPT"));
			$rule->executeInsert();
			
			// HTTP
			if ($httpLan && $httpWan)
			{
				$rule->setAllAttributes(array("chain_Name" => "INPUT", "protocol" => "tcp", "dport" => $httpPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($httpWan && !$httpLan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "tcp", "int_in" => $extIf, "dport" => $httpPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($httpLan && !$httpWan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "tcp", "int_in" => $intIf, "dport" => $httpPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}

			RouterSettings::saveSetting("HTTP_PORT", $httpPort);
			RouterSettings::saveSetting("WAN_HTTP_ENABLED", $httpWan ? 1 : 0);
			RouterSettings::saveSetting("LAN_HTTP_ENABLED", $httpLan ? 1 : 0);
			
			// SSH
			if ($sshWan && $sshLan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "tcp", "dport" => $sshPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($sshWan && !$sshLan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "tcp", "int_in" => $extIf, "dport" => $sshPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($sshLan && !$sshWan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "tcp", "int_in" => $intIf, "dport" => $sshPort, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			
			RouterSettings::saveSetting("SSH_PORT", $sshPort);
			RouterSettings::saveSetting("WAN_SSH_ENABLED", $sshWan ? 1 : 0);
			RouterSettings::saveSetting("LAN_SSH_ENABLED", $sshLan ? 1 : 0);
			
			// ICMP
			if ($icmpWan && $icmpLan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "icmp", "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($icmpWan && !$icmpLan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "icmp", "int_in" => $extIf, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			else if ($icmpLan && !$icmpWan)
			{
				$rule->setAllAttributes(array("chain_name" => "INPUT", "protocol" => "icmp", "int_in" => $intIf, "target" => "ACCEPT"));
				$rule->executeInsert();
			}
			
			RouterSettings::saveSetting("WAN_ICMP_ENABLED", $icmpWan ? 1 : 0);
			RouterSettings::saveSetting("LAN_ICMP_ENABLED", $icmpLan ? 1 : 0);
			
			// Save changes to firewall
			$iptablesRestore = IPTablesFwFilterTranslator::setSystemFromDb($tempDb);
			FileUtils::writeToFile(RouterSettings::getSettingValue("ELWOOD_CFG_DIR") . "/firewall/filter.rules", implode("\n", $iptablesRestore) . "\n");
			
			// Restart services, if needbe			
			if ($httpService->getPort() != $httpPort)
			{
				$httpService->setPort($httpPort);
				$httpService->save();
				$httpService->restart();
			}
			
			if ($sshService->getPort() != $sshPort)
			{
				$sshService->setPort($sshPort);
				$sshService->save();
				$sshService->restart();
			}
			
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