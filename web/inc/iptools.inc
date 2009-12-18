<?php
   function isValidIp($ipAddress)
   # Preconditions:  None
   # Postconditions: Returns true if $ipAddress is a valid IP address,
   #                 false if it is not.
   {
      # Check to make sure $ipAddress is in the format xxx.xxx.xxx.xxx
      if (ereg("(^[0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3}$)", $ipAddress))
      {
         # Check individual octets to make sure value is within a valid ip range (0-254)
         $octetList = preg_split("/\./", $ipAddress);
         foreach ($octetList as $value)
         {
            if ($value < 0 || $value > 255)
               return false;
         }

         # Check if first or last octet contains a value of 0
         if ($octetList[0] == 0 || $octetList[3] == 0)
           return false;

         # Check if last octet contains a value of 255
         if ($octetList[3] == 255)
            return false;
      }
      else
         return false;

      return true;
   }

   function isValidNetmask($netmask)
   # Preconditions:  None
   # Postconditions: Returns true if $netmask is a valid subnet mask,
   #                 false if it is not.
   {
      # Check to make sure $netmask is in the format xxx.xxx.xxx.xxx
      if (ereg("(^[0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3}$)", $netmask))
      {
         # Check individual octets to make sure they are valid netmask values
         $octetList = preg_split("/\./", $netmask);
         foreach ($octetList as $value)
         {
            if ($value != 255 && $value != 254 && $value != 252 && $value != 248 && $value != 240 && $value != 192 && $value != 128 && $value != 0)
               return false;
         }

         # Check if the first octet contains a value of 0 or last octet contains a value of 255
         if ($octetList[0] == 0 || $octetList[3] == 255)
            return false;
      }
      else
         return false;

      return true;
   }

   function isValidMac($mac)
   # Preconditions:  None
   # Postconditions: Returns true if $mac is a valid MAC address,
   #                 false if it is not
   {
      # Check to make sure $mac is in the format xx:xx:xx:xx:xx:xx with valid values
      if (ereg("(^[0-9A-Fa-f]{2}):([0-9A-Fa-f]{2}):([0-9A-Fa-f]{2}):([0-9A-Fa-f]{2}):([0-9A-Fa-f]{2}):([0-9A-Fa-f]{2}$)", $mac))
         return true;
      else
         return false;
   }

   function isValidPort($portNum)
   # Preconditions:  None
   # Postconditions: Returns if $portNum is a valid port number,
   #                 false if it is not
   {
      # Verify $portNum contains only numbers and is between 1-5 digits
      if (ereg("(^[0-9]{1,5}$)", $portNum))
      {
         # Verify port number is valid
         if ($portNum > 0 && $portNum <= 65535)
            return true;
         else
            return false;
      }
      else
         return false;
   }
?>
