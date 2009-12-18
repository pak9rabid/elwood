<?php
   ################################################
   # File :       user_change.php                 #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        2-05-2005                       #
   #                                              #
   # Description: Determine what type of change   #
   #              is being performed on the user  #
   #              and act accordingly.            #
   ################################################

   # Include required files
   require_once "usertools.inc";
   require_once "routersettings.inc";
   
   # Get current user and group user is associated with
   $currentUser       = getUser();
   $currentGroupgroup = getGroup($user);

   # Get input from the calling form
   $username = $_POST['username'];
   $password = $_POST['password'];
   $confirm  = $_POST['confirm'];

   # Determine if this is a password change for the current user
   # or something else and react accordingly
   if ($username == $currentUser)
   {
      # Path to htpasswd binary file
      $USER_HTPASSWD = "sudo ../bin/user_htpasswd";

      # Path to htpasswd file
      $HTPASSWD_FILE = getHttpdDir() . "/users";
      
      # If $password matches $confirm, change the password
      if ($password == $confirm)
      {
         $changePwCmd = "$USER_HTPASSWD chpass $currentUser $password";
         echo `$changePwCmd`;

         # Set to redirect to calling page (access.php)
         $redirect = "Location: ../access.php";
      }
      else
      {
         # Redirect to calling page with an error
         $redirect = "Location: ../access.php?error=pass2";
      }
   }
   else
   {
      # Set to redirect to the admin-level script
      $redirect = "Location: admin/change_users.php";

      $firstTime = true;
      foreach ($_POST as $key => $value)
      {
         if ($firstTime)
         {
            $redirect .= "?";
            $firstTime = false;
         }
         else
            $redirect .= "&";

         $redirect .= "$key" . "=" . "$value";
      }

   }

   # Redirect
   header($redirect);
?>
