<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class LoginAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
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
				$this->response = new AjaxResponse("", array("Invalid username and/or password"));
				return;
			}
			
			foreach ($userResult as $user)
				$_SESSION['user'] = serialize($user);
				
			$this->response = new AjaxResponse();
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
	}
?>