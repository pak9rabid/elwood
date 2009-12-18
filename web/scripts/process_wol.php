<?php
   ################################################
   # File :       process_wol.php                 #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        11-9-2005                       #
   #                                              #
   # Description: Wake up specified MAC address   #
   #              via wake on LAN                 #
   ################################################

   # Include required files
   require_once "iptools.inc";
   require_once "routersettings.inc";

   # Set base redirect
   $redirect = "Location: ../wol.php";

   # Get data from calling form
   $macAddr     = $_POST['mac_addr'];
   $showOptions = $_POST['show_options'];

   # Initialize boolean var to determine if an error exists
   $isError = false;

   # Check to make sure $macAddr is a valid MAC address
   if (!isValidMac($macAddr))
   {
      $redirect .= "?errmac=true&mac=$macAddr";
      $isError = true;
   }
   else
   {
      # Construct wol command to execute
      $wolCmd = getWol();

      # Check to see if show options is checked
      if ($showOptions)
      {
         # If additional options are entered, verify it contains
         # valid data

         # Pull data from form
         $password = $_POST['password'];
         $port     = $_POST['port'];
         $host     = $_POST['host'];

         # Check if password is in a valid MAC address format
         if ($password != "") 
         {
            if (!isValidMac($password))
            {
               $redirect .= "?errpass=true";
               $isError = true;
            }
            else
               $wolCmd .= " --passwd=$password";
         }

         # Check to see if $port is a valid port value
         if ($port != "")
         {
            if (!isValidPort($port))
            {
               if ($isError) 
                  $redirect .= "&errport=true";
               else
               {
                  $redirect .= "?errport=true";
                  $isError = true;
               }
            }
            else
               $wolCmd .= " --port=$port";
         }

         # If $host is not empty, append to $wolCmd
         if ($host != "")
            $wolCmd .= " --host=$host";
      }

      $wolCmd .= " $macAddr";

      # Execute wol command and send success message
      # back to calling page if no errors are present
      if (!$isError)
      {
         $output = `$wolCmd`;
         $redirect .= "?success=true&mac=$macAddr";
      }
   }

   # Redirect to calling page (wol.php)
   header($redirect);
?>
