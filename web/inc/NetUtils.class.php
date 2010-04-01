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
		
		public static function net2CIDR($fullAddress)
		{
			list($ip, $netmask) = preg_split("/\//", $fullAddress);
			
			if (!self::isValidIp($ip))
				throw new Exception("Error: Invalid IP to convert");
			
			//if (count($ipElements) == 1 || preg_match("/^[0-9]{1,2}$/", $ipElements[1]))
			if ($netmask == null || preg_match("/^[0-9]{1,2}$/", $netmask))
				return $fullAddress;
				
			return $ip . "/" . self::mask2CIDR($netmask);
		}
		
		public static function isValidIp($ip)
		{
			return $ip == long2ip(ip2long($ip));
		}
		
		public static function isValidNetwork($network)
		{
			// Network in forms 'xxx.xxx.xxx.xxx/xxx.xxx.xxx.xxx'
			// and 'xxx.xxx.xxx.xxx/xx' are valid
			list($ip, $netmask) = preg_split("/\//", $network);
			
			if (!self::isValidIp($ip) || !self::isValidNetmask($netmask))
				return false;
				
			return true;
		}
		
		public static function isValidNetmask($netmask)
		{
			if (self::isValidIp($netmask))
			{	
				// IP notation (xxx.xxx.xxx.xxx)
 				if(strlen(decbin(ip2long($netmask))) != 32 && ip2long($netmask) != 0)
  					return false;
  				
				if(preg_match("/01/", decbin(ip2long($netmask))) || (!preg_match("/0/", decbin(ip2long($netmask))) && $netmask != "255.255.255.255"))
  					return false;
			}
			else
			{
				// CIDR notation (/xx)
				if (!preg_match("/^[0-9]{1,2}$/", $netmask))
					return false;
					
				if ($netmask < 0 || $netmask > 32)
					return false;
			}
			
			return true;
		}
		
		public static function isValidIanaPortNumber($port)
		{
			if (!preg_match("/^[0-9]{1,5}$/", $port))
				return false;
				
			if ($port < 1 || $port > 65535)
				return false;
				
			return true;
		}
		
		public static function isValidProtocol($protocol)
		{
			return in_array($protocol, self::getNetworkProtocols());
		}
		
		public static function isValidIcmpType($icmpType)
		{	
			return in_array($icmpType, self::getIcmpTypes());
		}


		public static function isValidConnectionStates(Array $connStates)
		{
			foreach ($connStates as $connState)
			{
				if (!in_array($connState, self::getConnectionStates()))
					return false;
					
				return true;
			}
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
		
		public static function getNetworkProtocols()
		{
			return array("ip", "tcp", "udp", "icmp", "gre", "esp", "ah");
		}
		
		public static function getIcmpTypes()
		{
			return array	(
								"echo-request",
								"echo-reply",
								"destination-unreachable",
								"source-quench",
								"redirect",
								"router-advertisement",
								"router-solicitation",
								"time-exceeded",
								"parameter-problem",
								"timestamp-request",
								"timestamp-reply",
								"address-mask-request",
								"address-mask-reply"
							);
		}
		
		public static function getConnectionStates()
		{
			return array	(
								"ESTABLISHED",
								"INVALID",
								"NEW",
								"RELATED"
							);
		}
	}
?>