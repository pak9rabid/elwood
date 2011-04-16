<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	require_once "NetUtils.class.php";
	
	class AddEditNATOneToOneRuleAjaxRequestHandler implements AjaxRequestHandler
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
			$outsideAddr = $parameters['outsideAddr'];
			$insideAddr = $parameters['insideAddr'];
			
			$errors = array();
			
			if (!NetUtils::isValidIp($outsideAddr))
				$errors[] = "Invalid outside address specified";
				
			if (!NetUtils::isValidIp($insideAddr))
				$errors[] = "Invalid inside address specified";
				
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