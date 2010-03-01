<?php
	class NetUtils
	{
		public static function mask2CIDR($netmask)
		{
			if (!self::isValidNetmask($netmask))
				throw new Exception("Error: Invalid netmask to convert");	
			
			$long = ip2long($netmask);
			$base = ip2long('255.255.255.255');
			return 32-log(($long ^ $base)+1, 2);
		}
		
		public static function net2CIDR($ip)
		{
			$ipElements = preg_split("/\//", $ip);
			
			if (!self::isValidIp($ipElements[0]))
				throw new Exception("Error: Invalid IP to convert");
			
			if (count($ipElements) == 1 || preg_match("/^[0-9]{1,2}$/", $ipElements[1]))
				return $ip;
				
			return $ipElements[0] . "/" . self::mask2CIDR($ipElements[1]);
		}
		
		public static function isValidIp($ip)
		{
			return $ip == long2ip(ip2long($ip));
		}
		
		public static function isValidNetmask($netmask)
		{
			if (!self::isValidIp($netmask))
				return false;
				
 			if(strlen(decbin(ip2long($netmask))) != 32 && ip2long($netmask) != 0)
  				return false;
  				
			if(preg_match("/01/", decbin(ip2long($netmask))) || (!preg_match("/0/", decbin(ip2long($netmask))) && $netmask != "255.255.255.255"))
  				return false;
  				
  			return true;
		}
		
		public static function isValidMac($mac)
		{
			$mac = preg_replace("/[^0-9A-Fa-f]/", "", $mac);
			
			if (strlen($mac) != 12)
				return false;
				
			return true;
		}
		
		public static function toStandardMac($mac)
		{
			// Converts $mac to xx:xx:xx:xx:xx:xx notation
			if (!self::isValidMac($mac))
				throw new Exception("Error: Invalid MAC address to convert");
				
			$mac = preg_replace("/[^0-9A-Fa-f]/", "", $mac);
			$mac = str_split($mac, 2);
			
			return implode(":", $mac);
		}
	}
?>