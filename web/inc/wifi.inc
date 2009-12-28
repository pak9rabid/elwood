<?php
   # Include required files
   require_once "RouterSettings.class.php";

   class wifiConfig
   {
      # Wifi setting attributes
      var $isWifiEnabled;
      var $isSsidBcastEnabled;
      var $ssid;
      var $isWepEnabled;
      var $wepKey;
      var $wepMode;

      function wifiConfig()
      # Default constructor: initialize variables with current
      # router wifi settings
      {
         # Setting info from elwood.conf
         $BIN_PREFIX = RouterSettings::getSettingValue("ELWOOD_WEBROOT") . "/bin";
         $wlanIf     = RouterSettings::getSettingValue("LAN_WLAN");

         # Check to see if wlan NIC is enabled
         $checkNicEnabledCmd = "ifconfig | grep $wlanIf";

         $checkNicEnabled = `$checkNicEnabledCmd`;

         if ($checkNicEnabled)
            $this->isWifiEnabled = true;
         else
            $this->isWifiEnabled = false;

         # Check to see if broadcast SSID is enabled
         $this->isSsidBcastEnabled = true; // Card does not support disabling this

         # Get current SSID
         $getSsidCmd = "iwconfig $wlanIf | grep ESSID | cut -f2 -d':'";

         $this->ssid = trim(`$getSsidCmd`,"\"");

         # Check to see if WEP is enabled and get key
         $getWepKeyCmd = "sudo $BIN_PREFIX/show_iwconfig | grep Encryption\ key | cut -f2 -d':' | cut -f1 -d' '";

         $this->wepKey = trim(`$getWepKeyCmd`);

         if ($this->wepKey == "off")
            $this->isWepEnabled = false;
         else
            $this->isWepEnabled = true;

         # Get WEP mode
         $getWepModeCmd = "sudo $BIN_PREFIX/show_iwconfig | grep Security\ mode | cut -f3 -d':'";
         
         $this->wepMode = trim(`$getWepModeCmd`);
      }
   }
?>
