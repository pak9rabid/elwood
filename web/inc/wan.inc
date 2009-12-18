<?php
   # Include required files
   require_once "status.inc";
   require_once "routersettings.inc";

   class wanConfig
   {
      # WAN setting attributes
      var $useDhcp;
      var $ipAddress;
      var $netmask;
      var $gateway;
      var $dns1;
      var $dns2;
      var $dns3;
      var $mtuSize;

      function wanConfig()
      # Default constructor: intializes variables with current
      # router WAN settings
      {
         # Get router needed router settings
         $extIf = getExtIf();
         $intIf = getIntIf();

         $BIN_PREFIX = getElwoodWebroot() . "/bin";

         # Paths to files
	 $dhclientPidFile = getDhclientPidPath();
         
         # Check to see if the DHCP client is enalbed or disabled
         if (!file_exists($dhclientPidFile))
            $this->useDhcp = false;
         else
	 {
	    $isDhclientRunningCmd = "ps `sudo {$BIN_PREFIX}/show_dhclient_pid $extIf` | wc -l";

	    $isDhclientRunningResult = `$isDhclientRunningCmd`;

	    if ($isDhclientRunningResult > 1)
	       $this->useDhcp = true;
	    else
	       $this->useDhcp = false;
	 }

         # Pull the rest of the WAN settings by probing the system
         $routerStatus = new routerStatus;

         $this->ipAddress = $routerStatus->wanIP;
         $this->dns1      = $routerStatus->dnsList[0];
         $this->dns2      = $routerStatus->dnsList[1];
         $this->dns3      = $routerStatus->dnsList[2];

         # Commands used to pull WAN settings info not provided by $routerStatus
         $getNetmaskCmd = "/sbin/ifconfig $extIf | grep Mask | cut -f4 -d:";
         $getGatewayCmd = "/sbin/route -n | grep UG | cut -b17-31";
         $getMtuSizeCmd = "/sbin/ifconfig $extIf | grep MTU | cut -f2 -d':' | cut -f1 -d' '";

         # Execute commands and store results
         $this->netmask      = `$getNetmaskCmd`;
         $this->gateway      = `$getGatewayCmd`;
         $this->mtuSize      = `$getMtuSizeCmd`;

         # Strip off whitespaces and newlines from variables
         $this->netmask      = trim($this->netmask);
         $this->gateway      = trim($this->gateway);
         $this->mtuSize      = trim($this->mtuSize);
      }
   }
?>
