<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class AddEditUserAjaxRequestHandler implements AjaxRequestHandler
	{
		private $response;
		
		// Override
		public function processRequest(array $parameters)
		{
			$action = $parameters['action'];
			$username = $parameters['username'];
			$passwd = $parameters['passwd'];
			$confPasswd = $parameters['confPasswd'];
			$groupname = $parameters['groupname'];
					
			$currentUser = User::getUser();
			$errors = array();
			
			// validate global parameters
			if (empty($username))
				$errors[] = "No username specified";
							
			if ($action == "add")
			{
				// adding a new user
				if (!$currentUser->isAdminUser())
				{
					$this->response = new AjaxResponse("", array("Only admin users can add new users"));
					return;
				}
				
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (!empty($selectedUsers))
				{
					$this->response = new AjaxResponse("", array("The specified username is not available"));
					return;
				}
				
				if (empty($passwd))
					$errors[] = "No password specified";
				else if ($passwd != $confPasswd)
					$errors[] = "Password and confirm password do not match";
				
				if (!User::isValidGroup($groupname))
					$errors[] = "Invalid group name specified";
				
				if (!empty($errors))
				{
					$this->response = new AjaxResponse("", $errors);
					return;
				}
				
				$newUser = new User();
				$newUser->setAttribute("username", $username);
				$newUser->setPassword($passwd);
				$newUser->setAttribute("usergroup", $groupname);
				$newUser->executeInsert();
			}
			else if ($action == "edit")
			{
				// editing an existing user
				if (!$currentUser->isAdminUser())
				{
					$this->response = new AjaxResponse("", array("Only admin users can edit users"));
					return;
				}
				
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (empty($selectedUsers))
				{
					$this->response = new AjaxResponse("", array("The specified user to edit does not exist"));
					return;
				}
				
				foreach ($selectedUsers as $editUser)
				{
					if (!empty($passwd))
					{
						 if ($passwd != $confPasswd)
							$errors[] = "Password and confirm password do not match";
						else
							$editUser->setPassword($passwd);
					}
					
					if (!User::isValidGroup($groupname))
						$errors[] = "Invalid group name specified";
						
					if (!empty($errors))
					{
						$this->response = new AjaxResponse("", $errors);
						return;
					}
					
					if ($groupname != $editUser->getAttribute("usergroup"))
						$editUser->setAttribute("usergroup", $groupname);
					
					$editUser->executeUpdate();
				}
			}
			else if ($action == "pw")
			{
				// changing a password
				if (!$currentUser->isAdminUser() && $username != $currentUser->getAttribute("username"))
				{
					$this->response = new AjaxResponse("", array("Only admin users can change other users' password"));
					return;
				}
				
				if (empty($passwd))
					$errors[] = "No password specified";
				else if ($passwd != $confPasswd)
					$errors[] = "Password and confirm password do not match";
					
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (empty($selectedUsers))
				{
					$this->response = new AjaxResponse("", array("The specified user to change the password for does not exist"));
					return;
				}
				
				if (!empty($errors))
				{
					$this->response = new AjaxResponse("", $errors);
					return;
				}
				
				foreach ($selectedUsers as $changePwUser)
				{
					$changePwUser->setPassword($passwd);
					$changePwUser->executeUpdate();
				}
			}
			else
				// invalid action
				$this->response = new AjaxResponse("", array("Missing or invalid action specified"));
				
			$this->response = new AjaxResponse("Changes to users saved successfully");
		}
		
		// Override
		public function getResponse()
		{
			return $this->response;
		}
		
		
	}
?>