<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "User.class.php";
	
	class AddEditUserAjaxRequestHandler implements AjaxRequestHandler
	{
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
					return new AjaxResponse("", array("Only admin users can add new users"));
				
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (!empty($selectedUsers))
					return new AjaxResponse("", array("The specified username is not available"));
				
				if (empty($passwd))
					$errors[] = "No password specified";
				else if ($passwd != $confPasswd)
					$errors[] = "Password and confirm password do not match";
				
				if (!User::isValidGroup($groupname))
					$errors[] = "Invalid group name specified";
				
				if (!empty($errors))
					return new AjaxResponse("", $errors);
				
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
					return new AjaxResponse("", array("Only admin users can edit users"));
				
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (empty($selectedUsers))
					return new AjaxResponse("", array("The specified user to edit does not exist"));
				
				foreach ($selectedUsers as $editUser)
				{
					if (!empty($passwd))
					{
						 if ($passwd != $confPasswd)
							$errors[] = "Password and confirm password do not match";
						else
							$editUser->setPassword($passwd);
					}
					else
						$errors[] = "No password specified";
					
					if (!User::isValidGroup($groupname))
						$errors[] = "Invalid group name specified";
						
					if (!empty($errors))
						return new AjaxResponse("", $errors);
					
					if ($groupname != $editUser->getAttribute("usergroup"))
						$editUser->setAttribute("usergroup", $groupname);
					
					$editUser->executeUpdate();
				}
			}
			else if ($action == "pw")
			{
				// changing a password
				if (!$currentUser->isAdminUser() && $username != $currentUser->getAttribute("username"))
					return new AjaxResponse("", array("Only admin users can change other users' password"));
				
				if (empty($passwd))
					$errors[] = "No password specified";
				else if ($passwd != $confPasswd)
					$errors[] = "Password and confirm password do not match";
					
				$selectUser = new User();
				$selectUser->setAttribute("username", $username);
				$selectedUsers = $selectUser->executeSelect();
				
				if (empty($selectedUsers))
					return new AjaxResponse("", array("The specified user to change the password for does not exist"));
				
				if (!empty($errors))
					return new AjaxResponse("", $errors);
				
				foreach ($selectedUsers as $changePwUser)
				{
					$changePwUser->setPassword($passwd);
					$changePwUser->executeUpdate();
				}
			}
			else
				// invalid action
				return new AjaxResponse("", array("Missing or invalid action specified"));
				
			return new AjaxResponse("Changes to users saved successfully");
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>