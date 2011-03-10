<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	require_once "NetUtils.class.php";
	
	class AddEditNATIncomingRuleAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
			{
				$this->response = new AjaxResponse("", array("Only admin users are allowed to add or edit firewall rules"));
				return;
			}
			
			$id = $parameters['id'];
			$protocol = $parameters['protocol'];
			$port = $parameters['port'];
			$dstAddr = $parameters['dstAddr'];
			$dstPort = $parameters['dstPort'];
			
			$errors = array();
			
			if (!in_array($protocol, array("tcp", "udp")))
				$errors[] = "Invalid protocol specified";
				
			if (!NetUtils::isValidIanaPortNumber($port))
				$errors[] = "Invalid port number specified";
				
			if (!NetUtils::isValidIp($dstAddr))
				$errors[] = "Invalid destination address specified";
				
			if (!NetUtils::isValidIanaPortNumber($dstPort))
				$errors[] = "Invalid destination port specified";
				
			if (!empty($errors))
			{
				$this->response = new AjaxResponse("", $errors);
				return;
			}
			
			$this->response = new AjaxResponse(empty($id) ? "new" . uniqid() : $id, array());
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>