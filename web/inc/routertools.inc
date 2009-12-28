<?php
   # Include required files
   require_once "RouterSettings.class.php";
   require_once "iptools.inc";

   function writeToFile($filename, $output)
   # Preconditions:  $filename must have write permissions
   # Postconditions: Write $output to the specified file
   {
      if (is_writeable($filename))
      {
         if (!$fp = fopen($filename, 'w'))
            echo "\nError opening file $filename..\n";

         fwrite($fp, $output);
         fclose($fp);
      }
       else
         echo "\nFile $filename is not writeable..\n";
   }

   function toProtocolString($protocolIn)
   # Preconditions:  None
   # Postconditions: Returns a string interpretation of an entered protocol number
   #                 or the entered number/string if no match is found
   {
      # Path to protocols file
      $etcProtocolsFile = $PROTOCOLS;

      # Commands used to get protocol strings and number, respectively
      $getProtocolStringsCmd = "cat $etcProtocolsFile | grep -v ^# | cut -f1";
      $getProtocolNumsCmd    = "cat $etcProtocolsFile | grep -v ^# | cut -f2";

      # Initialize arrays used to store protocol strings and numbers
      $protocolStringList = array();
      $protocolNumsList = array();

      unset($protocolStringList);
      unset($protocolNumList);

      exec($getProtocolStringsCmd, $protocolStringList);
      exec($getProtocolNumsCmd, $protocolNumList);

      # If protocol number entered matches anything in $protocolNumList,
      # return associated protocol string
      foreach ($protocolNumList as $key => $value)
      {
         if ($value == $protocolIn)
            return $protocolStringList['$key'];
      }

      # Return entered string if no match is found
         return $protocolIn;
   }

   function trimList($listIn, $stripChars)
   # Preconditions:  $listIn must be an array
   # Postconditions: Returns an array with values stripped
   #                 specified in $stripChars
   {
      # Verify $listIn is an array
      if (is_array($listIn))
      {
         # Iterate thru array and remove $stripChars from each element
         foreach ($listIn as $key => $value)
            $listIn[$key] = trim($value, $stripChars);

         # Return result
         return $listIn;
      }
   }

   function getBroadcast($ipAddressIn, $netmaskIn)
   # Preconditions:  None
   # Postconditions: Returns the broadcast of the specified $ipAddressIn
   #                 with netmask of $netmaskIn, or false if the IP/netmask 
   #                 entered is invalid
   {
      # Set the bin prefix
      $BIN_PREFIX = RouterSettings::getSettingValue("ELWOOD_WEBROOT") . "/bin";

      # Trim ip and netmask
      $ipAddressIn = trim($ipAddressIn);
      $netmaskIn   = trim($netmaskIn);

      # Verify ip address and netmask passed in are valid
      if (!isValidIp($ipAddressIn) || !isValidNetmask($netmaskIn))
         return false;

      # Get the broadcast IP
      $getBroadcastCmd = "${BIN_PREFIX}/ipmask $netmaskIn $ipAddressIn | cut -f1 -d' '";

      return trim(`$getBroadcastCmd`);
   }

   function getNetwork($ipAddressIn, $netmaskIn)
   # Preconditions:  None
   # Postconditions: Returns the network IP of the specified $ipAddressIn
   #                 with netmask of $netmaskIn, or false if the IP/netmask 
   #                 entered is invalid
   {
      # Set the bin prefix
      $BIN_PREFIX = RouterSettings::getSettingValue("ELWOOD_WEBROOT") . "/bin";

      # Trim ip and netmask
      $ipAddressIn = trim($ipAddressIn);
      $netmaskIn   = trim($netmaskIn);

      # Verify ip address and netmask passed in are valid
      if (!isValidIp($ipAddressIn) || !isValidNetmask($netmaskIn))
         return false;

      # Get the network IP
      $getNetworkCmd = "${BIN_PREFIX}/ipmask $netmaskIn $ipAddressIn | cut -f2 -d' '";

      return trim(`$getNetworkCmd`);
   }
?>
