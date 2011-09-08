<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class RemoveUserAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			$userToRemove = $parameters['username'];
			
			if (!User::getUser()->isAdminUser())
				// you are not part of the 'admins' group
				return new AjaxResponse("", array("Only admin users are allowed to remove users"));
			
			if ($userToRemove == "admin")
				// cannot remove admin user
				return new AjaxResponse("", array("User 'admin' cannot be removed"));
			
			$userSelect = new User();
			$userSelect->setAttribute("username", $userToRemove);
			$matchedUsers = $userSelect->executeSelect();
			
			if (empty($matchedUsers))
				// user doesn't exist
				return new AjaxResponse("", array("The specified user does not exist"));
			
			foreach ($matchedUsers as $matchedUser)
				$matchedUser->executeDelete();
				
			return new AjaxResponse("User successfully removed");
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>