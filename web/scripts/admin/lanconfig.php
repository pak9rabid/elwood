<?
   #########################################
   # File:        lanconfig.php            #
   #                                       #
   # Author:      Patrick Griffin          #
   #                                       #
   # Date:        12-18-2004               #
   #                                       #
   # Description: Modifies LAN settings on #
   #              the router.              #
   #########################################

   # Include required files
   require_once "routertools.inc";
   require_once "usertools.inc";
   require_once "iptools.inc";
   require_once "routersettings.inc";

   # Set paths
   $INETD_DIR  = getInetdDir();
   $BIN_PREFIX = getElwoodWebroot() . "/bin";

   $lanConfigFile   = "${INETD_DIR}/lan.conf";
   $dhcpdConfigFile = "${INETD_DIR}/dhcpd.conf";

   # Network commands to execute
   $intIfRestart    = "sudo ${BIN_PREFIX}/intdown " . getIntIf() . " && " .
                      "sudo ${BIN_PREFIX}/intup " . getIntIf() .
		      "> /dev/null &";
   $dhcpdStop       = "sudo ${BIN_PREFIX}/dhcpdctrl stop";
   $dhcpdStart      = "sudo ${BIN_PREFIX}/dhcpdctrl start";
   $mergeNetConfigs = "sudo ${BIN_PREFIX}/merge_net_configs";

   # Redirection link
   $redirect = "Location: ../../lan.php";

   # Get current user
   $currentUser = getUser();

   # Store data from form into variables
   $ipAddress   = $_POST['ipaddress'];
   $netmask     = $_POST['netmask'];
   $dhcpServer  = $_POST['dhcpserver'];
   $startIp     = $_POST['startip'];
   $endIp       = $_POST['endip'];
   $dns1        = $_POST['dns1'];
   $dns2        = $_POST['dns2'];
   $dns3        = $_POST['dns3'];
   $newDhcpHost = $_POST['newdhcphost'];
   $newDhcpMAC  = $_POST['newdhcpmac'];
   $newDhcpIP   = $_POST['newdhcpip'];

   # Check that inputted data is valid
   $inputErrors = checkInput($ipAddress, $netmask, $dhcpServer, $startIp, $endIp, $dns1, $dns2, $dns3);
   $redirect .= $inputErrors;

   # If no errors, continue with configuring LAN interface
   if ($inputErrors == "")
   {
      # Initialize variable used for output
      $currentUser = getUser();
      $date = trim(`date`);

      ##########
      # Create lan.conf file based on input from form

      # Set internal interface settings
      $outToFile = "# " . getIntIf() . "\n" .
                   "auto " . getIntIf() . "\n" .
                   "iface " . getIntIf() . " inet static\n" .
                   "address $ipAddress\n" .
                   "netmask $netmask\n" .
                   "broadcast " . getBroadcast($ipAddress, $netmask) . "\n" .
                   "network " . getNetwork($ipAddress, $netmask) . "\n" .
                   "bridge_ports";

      # Check to see which LAN interfaces are available and bridge
      # accordingly
      if (getLanEthIf() != "none")
         $outToFile .= " " . getLanEthIf();

      if (getLanWlanIf() != "none")
         $outToFile .= " " . getLanWlanIn();

      # lan.conf file creation completed
      ##########

      # Write to lan config file
      writeToFile($lanConfigFile, $outToFile);

      # Stop dhcp server
      shell_exec($dhcpdStop);

      # Configure DHCP server only if it's enabled
      if ($dhcpServer == "enabled")
      {
         # Format $outToFile to configure DHCP server
         $getBroadcastCmd   = "/bin/ipmask $netmask $ipAddress | cut -f1 -d' '";
         $getNetworkCmd     = "/bin/ipmask $netmask $ipAddress | cut -f2 -d' '";
         $getNameserversCmd = "cat /etc/resolv.conf | cut -f2 -d' '";

         $broadcast = trim(`$getBroadcastCmd`);
         $network   = trim(`$getNetworkCmd`);

         $outToFile = "# dhcpd.conf\n" .
                      "#\n" .
                      "# Configuration file for ISC dhcpd (see 'man dhcpd.conf')\n" .
                      "# Generated automatically on $date by user $currentUser\n\n" .
                      "option subnet-mask $netmask;\n";

         if ($dns1 != "" || $dns2 != "" || $dns3 != "")
         {
            $outToFile .= "option domain-name-servers";
            if ($dns1 != "")
               $outToFile .= " $dns1";
            if ($dns2 != "")
            {
               if ($dns1 != "")
                  $outToFile .= ",";

               $outToFile .= " $dns2";
            }
            if ($dns3 != "")
            {
               if ($dns1 != "" || $dns2 != "")
                  $outToFile .= ",";

               $outToFile .= " $dns3";
            }
            $outToFile .= ";\n\n";
         }
         
         $outToFile .= "subnet $network netmask $netmask\n" .
                       "{\n" .
                       "   range $startIp $endIp;\n" .
                       "   option broadcast-address $broadcast;\n" .
                       "   option routers $ipAddress;\n" .
                       "   option subnet-mask $netmask;\n" .
                       "   default-lease-time 600;\n" .
                       "   max-lease-time 7200;\n" .
                       "}\n";
                   
         # Check for existing static dhcp addresses and add to config file
         # if any are set
         $count = 0;

         $hostKey = "dhcphost" . $count;

         while (array_key_exists($hostKey, $_POST))
         {
            $macKey = "dhcpmac" . $count;
            $ipKey  = "dhcpip" . $count;

            $tempHost = $_POST[$hostKey];
            $tempMAC  = $_POST[$macKey];
            $tempIP   = $_POST[$ipKey];

            $outToFile .= genDhcpStaticIpEntry($tempHost, $tempMAC, $tempIP);

            $count++;
            $hostKey = "dhcphost" . $count;
         }

         # Check for a new static dhcp address and add to config file
         # if a new one is specified
         $outToFile .= genDhcpStaticIpEntry($newDhcpHost, $newDhcpMAC,
	                                    $newDhcpIP);

         # Write to DHCP config file
         writeToFile($dhcpdConfigFile, $outToFile);
      }

      # Merge interface config files
      shell_exec($mergeNetConfigs);

      # Restart LAN interface
      shell_exec($intIfRestart);

      # Sleep for 5 seconds to give the internal interface
      # enough time to come back up after being restarted
      sleep(5);

      # Start dhcp server if it's enabled
      if ($dhcpServer == "enabled")
         shell_exec($dhcpdStart);
   }

   # Redirect to calling page (lan.php)
   header($redirect);

   ######################################
   ############ Functions ###############
   ######################################

   function genDhcpStaticIpEntry($hostname, $mac, $ip)
   # Preconditions:  None
   # Postconditions: Generates and returns dhcp.conf code for a static IP entry
   #                 or false if there is an error
   {
      if ($hostname != "" && isValidMac($mac) && isValidIp($ip))
      {
         return "\nhost $hostname\n" .
                "{\n" .
                "   hardware ethernet $mac;\n" .
                "   fixed-address $ip;\n" .
                "}\n";
      }

      return false;
   }

   function checkInput($ipAddress, $netmask, $dhcpServer, $startIp, $endIp, $dns1, $dns2, $dns3)
   # Preconditions:  None
   # Postconditions: Returns a string to append to the redirection link containing 
   #                 errors if the entered data is invalid, empty string is data
   #                 is valid
   {
      # String of errors appended to the redirection URL
      $errorString = "";

      # Make sure entered IP address is valid
      if ($ipAddress == "" || (! isValidIp($ipAddress)))
         $errorString = "?errip=true";

      # Make sure entered netmask is valid (blank netmask is not valid if IP type is static)
      if ($netmask == "" || (! isValidNetmask($netmask)))
      {
         if ($errorString == "")
            $errorString = "?errnetmask=true";
         Else
            $errorString .= "&errnetmask=true";
      }

      # Make sure entered start IP is valid
      if ( ($dhcpServer == "enabled") && (! isValidIp($startIp)) )
      {
         if ($errorString == "")
            $errorString = "?errstartip=true";
         else
            $errorString .= "&errstartip=true";
      }

      # Make sure entered end IP is valid
      if ( ($dhcpServer == "enabled") && (! isValidIp($endIp)) )
      {
         if ($errorString == "")
            $errorString = "?errendip=true";
         else
            $errorString .= "&errendip=true";
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
