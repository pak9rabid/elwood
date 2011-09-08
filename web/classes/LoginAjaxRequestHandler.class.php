<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class LoginAjaxRequestHandler implements AjaxRequestHandler
	{		
		// Override
		public function processRequest(array $parameters)
		{
			$username = $parameters['username'];
			$password = $parameters['password'];
			
			$userSelect = new User();
			$userSelect->setAttribute("username", $username);
			$userSelect->setAttribute("passwd", sha1($password));
			
			$userResult = $userSelect->executeSelect();
			
			if (empty($userResult))
			{
				unset($_SESSION['user']);
				return new AjaxResponse("", array("Invalid username and/or password"));
			}
			
			foreach ($userResult as $user)
				$_SESSION['user'] = serialize($user);
				
			return new AjaxResponse();
		}
		
		// Override
		public function isRestricted()
		{
			return false;
		}
	}
?>