<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class RemoveUserAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			$userToRemove = $parameters['username'];
			
			if (!User::getUser()->isAdminUser())
			{
				// you are not part of the 'admins' group
				$this->response = new AjaxResponse("", array("Only admin users are allowed to remove users"));
				return;
			}
			
			if ($userToRemove == "admin")
			{
				// cannot remove admin user
				$this->response = new AjaxResponse("", array("User 'admin' cannot be removed"));
				return;
			}
			
			$userSelect = new User();
			$userSelect->setAttribute("username", $userToRemove);
			$matchedUsers = $userSelect->executeSelect();
			
			if (empty($matchedUsers))
			{
				// user doesn't exist
				$this->response = new AjaxResponse("", array("The specified user does not exist"));
				return;
			}
			
			foreach ($matchedUsers as $matchedUser)
				$matchedUser->executeDelete();
				
			$this->response = new AjaxResponse("User successfully removed");
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>