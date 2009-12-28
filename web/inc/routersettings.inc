<?
   # Set path to elwood.db file
   define("ELWOOD_DB", "/etc/elwood/elwood.db");

   function getLanWlanIf()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns which interface is the lan wifi interface
   {
      $configFile = ELWOOD_CONF;

      # Commands used to get setting from config file
      $getLanWlanIfCmd = "cat $configFile | grep LAN_WLAN | cut -f2 -d'='";

      # Execute command and store into config variables
      $lanWlanIf = `$getLanWlanIfCmd`;

      # Trim and return result
      return trim($lanWlanIf);
   }

   function getLanEthIf()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns which interface is the lan ethernet interface
   {
      $configFile = ELWOOD_CONF;

      # Commands used to get setting from config file
      $getLanEthIfCmd = "cat $configFile | grep LAN_ETH | cut -f2 -d'='";

      # Execute command and store into config variables
      $lanEthIf = `$getLanEthIfCmd`;

      # Trim and return result
      return trim($lanEthIf);
   }

   function getExtIf()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns which interface is the external interface
   {
      $configFile = ELWOOD_CONF;

      # Commands used to get setting from config file
      $getExtIfCmd = "cat $configFile | grep EXTIF | cut -f2 -d'='";

      # Execute command and store into config variables
      $extIf = `$getExtIfCmd`;

      # Trim and return result
      return trim($extIf);
   }

   function getIntIf()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns which interface is the internal interface
   {
      $configFile = ELWOOD_CONF;
   
      # Commands used to get setting from config file
      $getIntIfCmd = "cat $configFile | grep INTIF | cut -f2 -d'='";

      # Execute command and store into config variable
      $intIf = `$getIntIfCmd`;
     
      # Trim and return result
      return trim($intIf);
   }

   function getElwoodWebroot()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the webroot directory of elwood
   {
      $configFile = ELWOOD_CONF;
   
      # Command used to get setting from config file
      $getElwoodWebrootCmd = "cat $configFile | grep ELWOOD_WEBROOT | cut -f2 -d'='";
      
      # Execute command and store into config variable
      $elwoodWebroot = `$getElwoodWebrootCmd`;
  
      # Trim and return result
      return trim($elwoodWebroot);
   }

   function getDhcpdConfig()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path to the dhcpd.conf file
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getDhcpdConfCmd = "cat $configFile | grep DHCPD_CONF | cut -f2 -d'='";

      # Execute command and store into config variable
      $dhcpdConfig = `$getDhcpdConfCmd`;

      # Trim and return result
      return trim($dhcpdConfig);
   }

   function getDhcpdPidPath()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path to the dhcpd pid file
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getDhcpdPidPathCmd  = "cat $configFile | grep DHCPD_PID_PATH | cut -f2 -d'='";

      # Execute command and store into config variable
      $dhcpdPidPath = `$getDhcpdPidPathCmd`;

      # Trim and return result
      return trim($dhcpdPidPath);
   }

   function getDhclientPidPath()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path to the dhclient pid file
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getDhclientPidPathCmd  = "cat $configFile | grep DHCLIENT_PID_PATH | cut -f2 -d'='";

      # Execute command and store into config variable
      $dhclientPidPath = `$getDhclientPidPathCmd`;

      # Trim and return result
      return trim($dhclientPidPath);
   }

   function getDhcpcdPath()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path to the dhcpd pid file
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getDhcpcdPathCmd = "cat $configFile | grep DHCPCD_DIR | cut -f2 -d'='";

      # Execute command and store into config variable
      $dhcpcdPath = `$getDhcpcdPathCmd`;

      # Trim and return result
      return trim($dhcpcdPath);
   }

   function getInetdDir()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path to the inetd dir
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getInetdDirCmd = "cat $configFile | grep INETD_DIR | cut -f2 -d'='";

      # Execute command and store into config variable
      $inetdDir = `$getInetdDirCmd`;

      # Trim and return result
      return trim($inetdDir);
   }

   function getHttpdDir()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns the path of the httpd dir
   {
      $configFile = ELWOOD_CONF;
  
      # Command used to get setting from config file
      $getHttpdDirCmd = "cat $configFile | grep HTTPD_DIR | cut -f2 -d'='";

      # Execute command and store into config variable
      $httpdDir = `$getHttpdDirCmd`;

      # Trim and return result
      return trim($httpdDir);
   }

   function getProtocols()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns path to the protocols file
   {
      $configFile = ELWOOD_CONF;

      # Command used to get settings from config file
      $getProtocolsCmd = "cat $configFile | grep PROTOCOLS | cut -f2 -d'='";

      # Execute command and store into config variable
      $protocols = `$getProtocolsCmd`;

      # Trim and return result
      return trim($protocols);
   }

   function getWol()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns path to the wol binary
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getWolCmd = "cat $configFile | grep WOL | cut -f2 -d'='";

      # #xecute command and store into config variable
      $wol = `$getWolCmd`;

      # Trim and return result
      return trim($wol);
   }

   function getFirewallDir()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns path to directory housing elwood firewall scripts
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getFirewallDirCmd = "cat $configFile | grep FIREWALL_DIR | cut -f2 -d'='";

      # Execute command and store into config variable
      $firewallDir = `$getFirewallDirCmd`;

      # Trim and return result
      return trim($firewallDir);
   }

   function getHistoryDir()
   # Preconditions:  /etc/elwood/elwood.conf must be a valid elwood config file
   # Postconditions: Returns path to directory housing elwood webterm history files
   {
      $configFile = ELWOOD_CONF;

      # Command used to get setting from config file
      $getHistoryDirCmd = "cat $configFile | grep ELWOOD_HISTORY | cut -f2 -d'='";

      # Execute commadn and store into config variable
      $historyDir = `$getHistoryDirCmd`;

      # Trim and return result
      return trim($historyDir);
   }
?>
