<?php
   # Include required files
   require_once "routersettings.inc";

   class routerStatus
   # Provides an object to view the network status of the router
   {
      # Used to store router status attributes
      var $wanIP;
      var $lanIP;
      var $uptime;
      var $dnsList          = array();
      var $activeIfacesList = array();

      function routerStatus()
      # Default contructor: initializes variables and populates
      # them with the current router status
      {
         # Path to file containing DNS info
         $dnsFile = "/etc/resolv.conf";

         # Get interfaces
         $EXTIF = getExtIf();
         $INTIF = getIntIf();

         # Commands used to grab status data
         $getWanIPCmd        = "/sbin/ifconfig $EXTIF | grep inet\ addr | cut -f2 -d: | cut -f1 -d' '";
         $getLanIPCmd        = "/sbin/ifconfig $INTIF | grep inet\ addr | cut -f2 -d: | cut -f1 -d' '";
         $getDnsCmd          = "cat $dnsFile | grep nameserver | cut -f2 -d' '";
         $getUptimeCmd       = "uptime | cut -f1 -d','";
         $getActiveIfacesCmd = "/sbin/ifconfig | grep Link\ encap | cut -f1 -d' '";

         # Execute commands and store results
         $this->wanIP        = `$getWanIPCmd`;
         $this->lanIP        = `$getLanIPCmd`;
         $this->uptime       = `$getUptimeCmd`;

         unset($this->dnsList,     $this->activeIfacesList);
         exec($getDnsCmd,          $this->dnsList);
         exec($getActiveIfacesCmd, $this->activeIfacesList);

         # Strip off whitespaces and newlines from variables
         $this->wanIP  = trim($this->wanIP);
         $this->lanIP  = trim($this->lanIP);
         $this->uptime = trim($this->uptime);

         foreach ($this->dnsList as $key => $value)
            $this->dnsList[$key] = trim($value);

         foreach ($this->activeIfacesList as $key => $value)
            $this->activeIfacesList[$key] = trim($value);
      }
   }
?>
