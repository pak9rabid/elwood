<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	require_once "NetUtils.class.php";
	
	class AddEditNATOneToOneRuleAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			if (!User::getUser()->isAdminUser())
				return new AjaxResponse("", array("Only admin users are allowed to add or edit firewall rules"));
			
			$id = $parameters['id'];
			$outsideAddr = $parameters['outsideAddr'];
			$insideAddr = $parameters['insideAddr'];
			
			$errors = array();
			
			if (!NetUtils::isValidIp($outsideAddr))
				$errors[] = "Invalid outside address specified";
				
			if (!NetUtils::isValidIp($insideAddr))
				$errors[] = "Invalid inside address specified";
				
			if (!empty($errors))
				return new AjaxResponse("", $errors);
			
			return new AjaxResponse(empty($id) ? "new" . uniqid() : $id, array());
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>