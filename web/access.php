<?php
   ################################################
   # File :       access.php                      #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        10-17-2005                      #
   #                                              #
   # Description: Display current access settings #
   #              on the router and provide an    #
   #              interface to change remote      #
   #              access changes.                 #
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "RouterSettings.class.php";

   # Get interfaces
   $EXTIF = RouterSettings::getSettingValue("EXTIF");
   $INTIF = RouterSettings::getSettingValue("INTIF");

   # Determine which group the current user belongs to
   $currentUser = getUser();
   $userGroup   = getGroup($currentUser);

   # Paths to files used
   $HTTPD_DIR = RouterSettings::getSettingValue("HTTPD_DIR");

   # Paths to commonly used programs
   $SHOWINPUT  = "sudo bin/show_input_table";
   $SHOWOUTPUT = "sudo bin/show_output_table";

   $usersFile  = "${HTTPD_DIR}/users";
   $groupsFile = "${HTTPD_DIR}/groups";

   # Figure out which methods of access are enabled by checking
   # firewall rules on the INPUT chain
   $getPortsCmd  = "$SHOWINPUT | grep : | cut -b88-100 | cut -f2 -d':'";
   $getIfacesCmd = "$SHOWINPUT | grep : | cut -b33-37 | cut -f1 -d' '";

   unset($ports);
   unset($ifaces);

   exec($getPortsCmd, $ports);
   exec($getIfacesCmd, $ifaces);

   $http_wan = 0;
   $http_lan = 0;
   $ssh_wan  = 0;
   $ssh_lan  = 0;

   $httpPort = 80;
   $sshPort  = 22;

   foreach ($ports as $key => $value)
   {
      if ($value == $httpPort)
      {
         if ($ifaces[$key] == "$EXTIF")
            $http_wan = 1;
         else
            $http_lan = 1;
      }
      if ($value == $sshPort)
      {
         if ($ifaces[$key] == "$EXTIF")
            $ssh_wan = 1;
         else
            $ssh_lan = 1;
      }
   }

   # Get a list of user web-interface account logins 
   # and group association on the router
   $getUsersCmd = "cat $usersFile | cut -f1 -d':'";

   $usersList = array();

   unset($usersList);
   exec($getUsersCmd, $usersList);
?>

<html>
   <head>
      <title>Remote Access Control</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
      <script language="JavaScript" src="inc/access.js" type="text/javascript"></script>
   </head>

   <body onLoad="javascript:setInputsForUser(<? echo "'$userGroup',
                                                      '$http_wan',
						      '$http_lan',
						      '$ssh_wan',
						      '$ssh_lan'"; ?>)">
      <div id="container">
         <div id="title">
            <? printTitle("Remote Access"); ?>
         </div>
         <? printNavigation(); ?>
         <div id="content">
         <font size="5"><b><u>General Access</u></b></font>
         <br><br>
         <form name="access_method" action="scripts/admin/change_access.php" method="POST">
            <table id="access-table">
               <tr>
	          <th>Access Method</th>
		  <th>WAN</th>
		  <th>LAN</th>
	       </tr>
               <tr>
	          <td>HTTP</td>
		  <td><input type='checkbox' name='httpwan' value='1'></td>
		  <td><input type='checkbox' name='httplan' value='1'></td>
	       </tr>
               <tr><td>SSH</td>
                  <td><input type='checkbox' name='sshwan' value='1'></td>
                  <td><input type='checkbox' name='sshlan' value='1'></td>
               </tr>
               </table>
               <br>
               <input name="submit" type="submit" value="Change">&nbsp
	       <input type="reset">
               <br>
               <font color="red" size="4"><u>WARNING:</u></font> If no access type is selected, the only access is through the console.
               </form>
               <hr>
               <font size="5"><b><u>Web Users</u></b></font>
               <br><br>
               <form name="users" action="scripts/user_change.php" method="POST">
               <table id="access-table">
                  <tr>
		     <th>Current Users</th>
		     <th>Group</th>
		     <th>Remove</th>
		  </tr>
		  <?php
		     # Print list of users
                     foreach ($usersList as $value)
                     {
                        $selectBox = writeGroupSelect($value);

                        if ($value == "admin")
                           echo "<tr><td>$value</td><td>admins</td><td>&nbsp</td></tr>";
                        else
                        {
                           $userVal = $value . "-rm";
                           echo "<tr><td>$value</td><td>$selectBox</td><td><input type='checkbox' name='$userVal' value='1'></td></tr>";
                        }
                      }
		   ?>
               </table>
               <br>
	       <?php
                  # If part of admins group, display option to add 
		  # new user or change a users password
                  # If part of the users group, display option to change
		  # current user password only
                  if ($userGroup == "admins")
                     echo "<b>Add User/Change Password</b>";
                  else
                     echo "<b>Change Password</b>";

                  echo "<br>";

                  # Check for errors and print appropriate error message
                  if ($_GET['error'] == "pass1")
                  {
                     echo "<br>";
                     echo "<font id='error-font'><b>Error: No password entered</b></font>";
                  }
                  else if ($_GET['error'] == "pass2")
                  {
                     echo "<br>";
                     echo "<font id='error-font'><b>Error: Password does not match confirmed password</b></font>";
                  }
                  else if ($_GET['error'] == "username")
                  {
                     echo "<br>";
                     echo "<font id='error-font'><b>Error: Username entered is not allowed</b></font>";
                  }

echo <<<END
               <br>
               <table id="status-table">
END;
               # If user is in group admins, display options to add
	       # user/change password
               if ($userGroup == "admins")
               {
echo <<<END
                  <tr>
		     <th>Username:</th>
		     <td><input id="textfield" name="username" length="10" maxlength="20"></td>
		  </tr>
                  <tr>
		     <th>Password:</th><td><input id="textfield" name="password" type="password" length="10" maxlength="20"></td>
		  </tr>
                  <tr>
		     <th>Confirm Password:</th>
		     <td><input id="textfield" name="confirm" type="password" length="10" maxlength="20"></td>
		  </tr>
END;
               }
               else
               {
echo <<<END
                  <input type="hidden" name="username" value="$currentUser">
                  <tr>
		     <th>New Password:</th>
		     <td><input id="textfield" name="password" type="password" length="10" maxlength="20"></td>
		  </tr>
                  <tr>
		     <th>Confirm Password:</th>
		     <td><input id="textfield" name="confirm" type="password" length="10" maxlength="20"></td>
		  </tr>
END;
               }
echo <<<END
               </table>
               <br>
               <input name="submit" type="submit" value="Change">&nbsp<input name="reset" type="reset">
               </form>
END;

               ##########################
               ####### Functions ########
               ##########################

	       function writeGroupSelect($user)
	       # Preconditions:  $user must be a valid user on the system
	       # Postconditions: Returns the code necessary to create a select box with the correct group
	       #                 $user is associated with selected  by default
	       {
                  $selectBoxName = $user . "-gs";
	          $output = "<select name='$selectBoxName'>"; 
                         
                  if (getGroup($user) == "admins")
                  {
                     $output .= "<option value='admins' selected>admins" .
                                "<option value='users'>users";
                  }
                  else
                  {
                     $output .= "<option value='admins'>admins" .
                                "<option value='users' selected>users";
                  }

                  $output .= "</select>";

                  return $output;
               }
	    ?>
	 </div>
      </div>
   </body>
</html>
