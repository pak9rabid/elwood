<?php
   # Include required files
   require_once "status.inc";
   require_once "RouterSettings.class.php";
   require_once "routertools.inc";

   class lanConfig
   {
      # LAN setting attributes
      var $dhcpServerEnabled;
      var $ipAddress;
      var $netmask;
      var $dhcpStartIp;
      var $dhcpEndIp;
      var $dns1;
      var $dns2;
      var $dns3;
      var $dhcpHostList;
      var $dhcpMACList;
      var $dhcpIPList;

      function lanConfig()
      # Default constructor: intializes variables with current
      # router LAN settings
      {
         # Get elwood.conf settings
         $BIN_PREFIX         = RouterSettings::getSettingValue("ELWOOD_WEBROOT") . "/bin";
         $extIf              = RouterSettings::getSettingValue("EXTIF");
         $intIf              = RouterSettings::getSettingValue("INTIF");
         $inetdConfigDirPath = RouterSettings::getSettingValue("INETD_DIR");
         $dhcpdPidFile       = RouterSettings::getSettingValue("DHCPD_PID_PATH");
         $dhcpdConfigFile    = RouterSettings::getSettingValue("DHCPD_CONF");
         
         # Check to see if the DHCP server is enabled or disabled
	 if (!file_exists($dhcpdConfigFile))
	    $this->dhcpServerEnabled = false;
	 else
	 {
	    $isDhcpdRunningCmd = "ps `sudo {$BIN_PREFIX}/show_dhcpd_pid` | wc -l";

	    $isDhcpdRunningCmdResult = `$isDhcpdRunningCmd`;

            if ($isDhcpdRunningCmdResult > 1)
               $this->dhcpServerEnabled = true;
            else
               $this->dhcpServerEnabled = false;
	 }

         # Pull the rest of the LAN settings by probing the system
         $routerStatus = new routerStatus;

         $this->ipAddress  = $routerStatus->lanIP;

         # Commands used to pull LAN settings info not provided by $routerStatus
         $getNetmaskCmd      = "/sbin/ifconfig $intIf | grep Mask | cut -f4 -d:";
         $getDhcpStartIpCmd  = "cat $dhcpdConfigFile | grep range | cut -f5 -d' '";
         $getDhcpEndIpCmd    = "cat $dhcpdConfigFile | grep range | cut -f6 -d' '";
         $getDns1Cmd         = "cat $dhcpdConfigFile | grep domain-name-servers | cut -f3 -d' '";
         $getDns2Cmd         = "cat $dhcpdConfigFile | grep domain-name-servers | cut -f4 -d' '";
         $getDns3Cmd         = "cat $dhcpdConfigFile | grep domain-name-servers | cut -f5 -d' '";
         $getDhcpHostListCmd = "cat $dhcpdConfigFile | grep 'host ' | cut -f2 -d' '";
         $getDhcpMACListCmd  = "cat $dhcpdConfigFile | grep 'hardware ethernet' | cut -f6 -d' '";
         $getDhcpIPListCmd   = "cat $dhcpdConfigFile | grep fixed-address | cut -f5 -d' '";

         # Initialize array used to store values
         $this->dhcpHostList = array();
         $this->dhcpMACList  = array();
         $this->dhcpIPList   = array();

         # Execute commands and store results
         $this->netmask     = `$getNetmaskCmd`;
         $this->dhcpStartIp = `$getDhcpStartIpCmd`;
         $this->dhcpEndIp   = `$getDhcpEndIpCmd`;
         $this->dns1        = `$getDns1Cmd`;
         $this->dns2        = `$getDns2Cmd`;
         $this->dns3        = `$getDns3Cmd`;

         exec($getDhcpHostListCmd, $this->dhcpHostList);
         exec($getDhcpMACListCmd,  $this->dhcpMACList);
         exec($getDhcpIPListCmd,   $this->dhcpIPList);

         # Stip off whitespaces, newlines, commas, and semi-colons from variables
         $stripChars = "\n,;";

         $this->netmask     = trim($this->netmask,     $stripChars);
         $this->dhcpStartIp = trim($this->dhcpStartIp, $stripChars);
         $this->dhcpEndIp   = trim($this->dhcpEndIp,   $stripChars);
         $this->dns1        = trim($this->dns1,        $stripChars);
         $this->dns2        = trim($this->dns2,        $stripChars);
         $this->dns3        = trim($this->dns3,        $stripChars);

         $this->dhcpHostList = trimList($this->dhcpHostList, $stripChars);
         $this->dhcpMACList  = trimList($this->dhcpMACList,  $stripChars);
         $this->dhcpIPList   = trimList($this->dhcpIPList,   $stripChars);
      }
   }
?>
