<?
   ############################################
   # File:          wanconfig.php             #
   #                                          #
   # Author:        Patrick Griffin           #
   #                                          #
   # Last Modified: 9-30-2005                 #
   #                                          #
   # Description:   Modifies WAN settings on  #
   #                the router.               #
   ############################################
   
   # Include required files
   require_once "routertools.inc";
   require_once "usertools.inc";
   require_once "iptools.inc";
   require_once "routersettings.inc";

   # Files used for WAN configuration
   $INETD_DIR  = getInetdDir();
   $BIN_PREFIX = getElwoodWebroot() . "/bin";

   $wanConfigFile = "$INETD_DIR/wan.conf";
   $dnsConfigFile = "/etc/resolv.conf";

   # Network commands to be executed
   $extIfDown       = "sudo ${BIN_PREFIX}/intdown " . getExtIf();
   $extIfUp         = "sudo ${BIN_PREFIX}/intup " . getExtIf() .
                      " > /dev/null &";
   $mergeNetConfigs = "sudo ${BIN_PREFIX}/merge_net_configs";

   # Redirection link
   $redirect = "Location: ../../wan.php";

   # Store data from form into variables
   $ipType       = $_POST['iptype'];
   $ipAddress    = $_POST['ipaddress'];
   $netmask      = $_POST['netmask'];
   $gateway      = $_POST['gateway'];
   $dns1         = $_POST['dns1'];
   $dns2         = $_POST['dns2'];
   $dns3         = $_POST['dns3'];
   $mtuSize      = $_POST['mtusize'];

   # Check that inputted data is valid
   $inputErrors = checkInput($ipType, $ipAddress, $netmask, $gateway, $dns1,
                             $dns2, $dns3);
   $redirect .= $inputErrors;

   # If no errors, continue with configuring WAN interface
   if ($inputErrors == "")
   {
      # Initialize variable used for output
      $currentUser = getUser();

      ##########
      # Create output for wan.conf
      $outToFile = "# " . getExtIf() . "\n" .
                   "auto " . getExtIf() . "\n" .
		   "allow-hotplug " . getExtIf() . "\n" .
		   "iface " . getExtIf() . " inet ";

      # Determine which method to configure the WAN interface
      # (DHCP or static ip)
      if ($ipType == "dynip")
      {
         # IP will be obtained via dhcp
         $outToFile .= "dhcp\n";
      }
      else
      {
         # IP will be set statically
	 $outToFile .= "static\n" .
	               "address $ipAddress\n" .
		       "netmask $netmask\n" .
		       "broadcast " . getBroadcast($ipAddress, $netmask) . "\n" .
		       "network " . getNetwork($ipAddress, $netmask) . "\n" .
		       "gateway $gateway\n" .
		       "mtu $mtuSize\n";
      }
      # wan.conf file creation completed
      ##########

      # Write to WAN config file
      writeToFile($wanConfigFile, $outToFile);

      ##########
      # Create output for DNS info and write to resolv.conf if IP is set
      # statically
      if ($ipType == "statip")
      {
         $outToFile = "# Generated automatically on $date by user " .
                      "$currentUser\n\n";

         if ($dns1 != "")
            $outToFile .= "nameserver $dns1\n";
         if ($dns2 != "")
            $outToFile .= "nameserver $dns2\n";
         if ($dns3 != "")
            $outToFile .= "nameserver $dns3\n";

	 # Write DNS info to resolv.conf
         writeToFile($dnsConfigFile, $outToFile);
      }
      # DNS file creation complete
      ##########

      # Bring down WAN interface
      shell_exec($extIfDown);

      # Merge interface config files
      shell_exec($mergeNetConfigs);

      # Bring WAN interface up
      shell_exec($extIfUp);

      # Sleep for 10 seconds if IP is pulled dynamically
      # to give it enough time to pull an IP or die trying
      if ($ipType == "dynip")
         sleep(10);

      $outToFile = "# Generated automatically on $date by user " .
                   "$currentUser\n\n";

      if ($dns1 != "")
         $outToFile .= "nameserver $dns1\n";
      if ($dns2 != "")
         $outToFile .= "nameserver $dns2\n";
      if ($dns3 != "")
         $outToFile .= "nameserver $dns3\n";

      # Write DNS info to resolv.conf if DNS fields are
      # not blank
      if ($dns1 != "" && $dns2 != "" && $dns2 != "")
         writeToFile($dnsConfigFile, $outToFile);
   }

   # Redirect to calling page (wan.php)
   header($redirect);

   #######################################
   ############# Functions ###############
   #######################################

   function checkInput($ipType, $ipAddress, $netmask, $gateway, $dns1, $dns2, $dns3)
   # Preconditions:  None
   # Postconditions: Returns a string to append to the redirection link containing 
   #                 errors if the entered data is invalid, empty string is data
   #                 is valid
   {
      # String of errors appended to the redirection URL
      $errorString = "";

      # Make sure entered IP address is valid (blank IP is not valid if IP type is static)
      if (($ipType == "statip" && $ipAddress == "") || ($ipAddress != "" && (! isValidIp($ipAddress))))
         $errorString = "?errip=true";

      # Make sure entered netmask is valid (blank netmask is not valid if IP type is static)
      if (($ipType == "statip" && $netmask == "") || ($netmask != "" && (! isValidNetmask($netmask))))
      {
         if ($errorString == "")
            $errorString = "?errnetmask=true";
         else
            $errorString .= "&errnetmask=true";
      }

      # Make sure entered gateway is valid
      if ($gateway != "" && (! isValidIp($gateway)))
      {
         if ($errorString == "")
            $errorString = "?errgw=true";
         else
            $errorString .= "&errgw=true";
      }

      # Make sure entered DNS is valid
      if ($dns1 != "" && (! isValidIp($dns1)))
      {
         if ($errorString == "")
            $errorString = "?errdns1=true";
         else
            $errorString .= "&errdns1=true";
      }

      if ($dns2 != "" && (! isValidIp($dns2)))
      {
         if ($errorString == "")
            $errorString = "?errdns2=true";
         else
            $errorString .= "&errdns2=true";
      }

      if ($dns3 != "" && (! isValidIp($dns3)))
      {
         if ($errorString == "")
            $errorString = "?errdns3=true";
         else
            $errorString .= "&errdns3=true";
      }

      return $errorString;
   }
?>
