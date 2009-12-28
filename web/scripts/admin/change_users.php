<?php
   ################################################
   # File: change_users.php                       #
   # Author: Patrick Griffin                      #
   # Date: 10-12-2005                             #
   # Description: Modifies users allowed to       #
   #              access the web interface.       #
   #                                              #
   ################################################

   # Include required files
   require_once "RouterSettings.class.php";

   # Path to htpasswd binary file to add users/change passwords
   $USER_HTPASSWD = "sudo ../../bin/user_htpasswd";

   # Path to htpasswd file
   $HTPASSWD_FILE = RouterSettings::getSettingValue("HTTPD_DIR") . "/users";

   # Known info from the form
   $username = $_GET['username'];
   $password = $_GET['password'];
   $confirm  = $_GET['confirm'];

   # Trim off any whitespaces from the username
   $username = trim($username);

   # Redirection header link
   $redirect = "Location: ../../access.php";

   # Verify that the username entered is an allowable identifier
   $badUsernameList = array("admins", "users", "-1");
   $invalidUsername = false;

   foreach ($badUsernameList as $value)
   {
      if ($username == $value)
      {
         $invalidUsername = true;
         break;
      }
   }

   # Check to see if the username is not allowed and redirect with
   # an error if it is
   if ($invalidUsername)
      $redirect .= "?error=username";
   else
   {
      # Get a list of all users and store them in $userList
      $getUsersCmd = "cat $HTPASSWD_FILE | cut -f1 -d:";
   
      unset($userList);
      exec($getUsersCmd, $userList);

      # Get a list of the users on the system, remove users marked for removal
      # and set the group for each user
      $userList    = array();
      $groupList   = array();
      $rmUsersList = array();

      foreach ($_GET as $key => $value)
      {
         if (preg_match("/-rm$/", $key))
            $rmUsersList[] = preg_replace("/-rm$/", "", $key);

         if (preg_match("/-gs$/", $key))
         {
            $userList[]  = preg_replace("/-gs$/", "", $key);
            $groupList[] = $_GET[$key];
         }
      }

      foreach($userList as $key => $value)
         exec("$USER_HTPASSWD chgroup $value $groupList[$key]");

      foreach ($rmUsersList as $value)
         exec("$USER_HTPASSWD remove $value");

      # If entered, add user or change password for user
      if ($username != "")
      {
         # Check to see if user already exist
         $userExists = false;
         foreach ($userList as $value)
         {
            if ($value == $username)
               $userExists = true;
         }

         # Check to make sure password and confirmed password match
         if ($password == $confirm && $password != "")
         {
            # Escape any characters that may execute arbitrary shell commands
            # Escaped twice intentionally because CPP program executes the command
            $password = escapeshellcmd($password);
            $password = escapeshellcmd($password);

            # Set password for user
            exec("$USER_HTPASSWD add $username $password");
         }
         else
         {
            if ($password == "")
               $redirect .= "?error=pass1";
            else
               $redirect .= "?error=pass2";
         }
      }
   }

   # Redirect to calling page (access.php)
   header($redirect);
?>
