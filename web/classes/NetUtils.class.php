<?php
	require_once "Net/IPv4.php";
	require_once "WirelessSecurity.class.php";
	require_once "SystemProfile.class.php";
	require_once "DataHash.class.php";
	
	class NetUtils
	{
		const MAX_SSID_LENGTH = 31;
		const MIN_PSK_PASSPHRASE_LENGTH = 8;
		const MAX_PSK_PASSPHRASE_LENGTH = 63;
		const MAX_WEP_KEY_LENGTH = 26;
		private static $WIRELESS_MODES = array("a", "b", "g");
		private static $WIRELESS_CHANNELS_24_GHZ = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11);
		private static $WIRELESS_CHANNELS_5_GHZ = array(36, 40, 44, 48, 52, 56, 60, 64, 100, 104, 108, 112, 116, 136, 140, 149, 153, 157, 161, 165);
		private static $IP_STATES = array("ESTABLISHED", "INVALID", "NEW", "RELATED");
		
		public static function mask2CIDR($netmask)
		{
			if (!self::isValidNetmask($netmask))
				throw new Exception("Invalid netmask to convert");	
			
			$long = ip2long($netmask);
			$base = ip2long('255.255.255.255');
			return 32-log(($long ^ $base)+1, 2);
		}
		
		public static function cidr2Mask($cidr)
		{
			if (!self::isValidCidr($cidr))
				throw new Exception("Invalid CIDR suffix to convert");
				
			if ($cidr == 0)
				return "0.0.0.0";
				
			return long2ip(-1 << (32 - (int)$cidr));
		}
		
		public static function net2CIDR($fullAddress)
		{
			list($ip, $netmask) = preg_split("/\//", $fullAddress);
			
			if (!self::isValidIp($ip))
				throw new Exception("Invalid IP to convert");
			
			if ($netmask == null || preg_match("/^[0-9]{1,2}$/", $netmask))
				return $fullAddress;
				
			return $ip . "/" . self::mask2CIDR($netmask);
		}
		
		public static function isValidIp($ip)
		{
			return $ip == long2ip(ip2long($ip));
		}
		
		public static function isValidAddress($address)
		{
			// address is in form <ip>/<CIDR netmask> (example: 10.0.0.1/24)
			$address = explode("/", $address);
			
			if (count($address) != 2)
				return false;
				
			list($ip, $cidr) = $address;
			
			if (!self::isValidIp($ip) || !self::isValidCidr($cidr))
				return false;
				
			return true;
		}
		
		public static function isValidNetwork($network)
		{
			// Network in forms 'xxx.xxx.xxx.xxx/xxx.xxx.xxx.xxx'
			// and 'xxx.xxx.xxx.xxx/xx' are valid
			list($ip, $netmask) = preg_split("/\//", $network);
			
			if (!self::isValidIp($ip) || (!self::isValidNetmask($netmask) && !self::isValidCidr($netmask)))
				return false;
				
			return true;
		}
		
		public static function isValidCidr($cidr)
		{
			if (!preg_match("/^[0-9]{1,2}$/", $cidr))
				return false;
					
			if ($cidr < 0 || $cidr > 32)
				return false;
				
			return true;
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
			}
			
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
				throw new Exception("Invalid MAC address to convert");
				
			$mac = preg_replace("/[^0-9A-Fa-f]/", "", $mac);
			$mac = str_split($mac, 2);
			
			return implode(":", $mac);
		}
		
		public static function getNetworkProtocols()
		{
			return array("tcp", "udp", "icmp", "gre", "esp", "ah");
		}
		
		public static function getIcmpTypes()
		{
			return array	(
								"any" => "any",
								"0" => "echo-reply",
								"3" => "destination-unreachable",
								"3/0" => "network-unreachable",
								"3/1" => "host-unreachable",
								"3/2" => "protocol-unreachable",
								"3/3" => "port-unreachable",
								"3/4" => "fragmentation-needed",
								"3/5" => "source-route-failed",
								"3/6" => "network-unknown",
								"3/7" => "host-unknown",
								"3/9" => "network-prohibited",
								"3/10" => "host-prohibited",
								"3/11" => "TOS-network-unreachable",
								"3/12" => "TOS-host-unreachable",
								"3/13" => "communication-prohibited",
								"3/14" => "host-precedence-violation",
								"3/15" => "precedence-cutoff",
								"4" => "source-quench",
								"5" => "redirect",
								"5/0" => "network-redirect",
								"5/1" => "host-redirect",
								"5/2" => "TOS-network-redirect",
								"5/3" => "TOS-host-redirect",
								"8" => "echo-request",
								"9" => "router-advertisement",
								"10" => "router-solicitation",
								"11" => "time-exceeded",
								"11/0" => "ttl-zero-during-transit",
								"11/1" => "ttl-zero-during-reassembly",
								"12" => "parameter-problem",
								"12/0" => "ip-header-bad",
								"12/1" => "required-option-missing",
								"17" => "address-mask-request",
								"18" => "address-mask-reply"
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
		
		public static function icmpCode2Text($icmpCode)
		{
			$icmpTypes = self::getIcmpTypes();
			return $icmpTypes[$icmpCode];
		}
		
		public static function isValidMtu($mtu)
		{
			if (!preg_match("/^[0-9]+$/", $mtu))
				return false;
				
			if ($mtu < 68 || $mtu > 9000)
					return false;
			
			return true;
		}
				
		public static function calculate($ip, $netmask = "")
		{
			// $ip can be in regular (with $netmask) or CIDR form
			// if $netmask is specified, $ip will be parsed as a regular IP (not in CIDR form)
			if (empty($netmask))
			{
				if (!self::isValidNetwork($ip))
					throw new Exception("Invalid CIDR address specified");
					
				list($ip, $netmask) = explode("/", $ip);
			}
			else
			{
				if (!self::isValidIp($ip))
					throw new Exception("Invalid IP specified");
					
				$netmask = self::mask2CIDR($netmask);
			}
			
			$netCalculator = new Net_IPv4();
			$netCalculator->ip = $ip;
			$netCalculator->bitmask = $netmask;
			
			$error = $netCalculator->calculate();
			
			if (is_object($error))
				throw new Exception($error->getMessage());
				
			return (object) array("network" => $netCalculator->network, "broadcast" => $netCalculator->broadcast);
		}
		
		public static function isValidSsid($ssid)
		{
			return (!empty($ssid) && strlen($ssid) <= self::MAX_SSID_LENGTH);
		}
		
		public static function isValidWirelessMode($mode)
		{
			return in_array($mode, self::$WIRELESS_MODES);
		}
		
		public static function isValidWirelessChannel($channel, $mode)
		{
			if (!self::isValidWirelessMode($mode))
				return false;
			
			$channels = array	(
									"a" => self::$WIRELESS_CHANNELS_5_GHZ,
									"b" => self::$WIRELESS_CHANNELS_24_GHZ,
									"g" => self::$WIRELESS_CHANNELS_24_GHZ,
									"n" => array_merge(self::$WIRELESS_CHANNELS_24_GHZ, self::$WIRELESS_CHANNELS_5_GHZ)
								);
												
			if (!in_array($channel, $channels[$mode]))
				return false;
				
			return true;
		}
		
		public static function isValidWirelessSecurityMethod($method)
		{			
			return in_array($method, self::getWirelessSecurityMethods());
		}
		
		public static function isValidWirelessAuthMethod($method)
		{
			return in_array($method, self::getWirelessAuthMethods());
		}
		
		public static function isValidWirelessKey($key, $securityMethod)
		{
			// $securityMethod is one of the defined constants in the WirelessSecurity class
			// for WEP, only 40 or 104 bit hex values are accepted
			// for WPA[2]_PSK, passphrases between 8 and 63 characters are accepted
			
			$validSecurityMethods = array	(
												WirelessSecurity::WEP,
												WirelessSecurity::WPA_PSK,
												WirelessSecurity::WPA2_PSK
											);
											
			if (!in_array($securityMethod, $validSecurityMethods))
				return false;
							
			if ($securityMethod == WirelessSecurity::WEP)
			{
				// WEP
				if (!preg_match("/^([0-9A-Fa-f]{10}|[0-9A-Fa-f]{26})$/", $key))
					return false;
					
				return true;
			}
			else
			{
				// WPA passphrase
				return (strlen($key) >= self::MIN_PSK_PASSPHRASE_LENGTH && strlen($key) <= self::MAX_PSK_PASSPHRASE_LENGTH); 
			}
		}
		
		public static function getWirelessSecurityMethods()
		{
			return array	(
								WirelessSecurity::NONE,
								WirelessSecurity::WEP,
								WirelessSecurity::WPA_PSK,
								WirelessSecurity::WPA_EAP,
								WirelessSecurity::WPA2_PSK,
								WirelessSecurity::WPA2_EAP
							);
		}
		
		public static function getWirelessAuthMethods()
		{			
			return array	(
								WirelessSecurity::AUTH_OPEN,
								WirelessSecurity::AUTH_SHARED_KEY
							);
		}
		
		public static function getWirelessModes()
		{
			return self::$WIRELESS_MODES;
		}
		
		public static function getWirelessChannels24()
		{
			return self::$WIRELESS_CHANNELS_24_GHZ;
		}
		
		public static function getWirelessChannels5()
		{
			return self::$WIRELESS_CHANNELS_5_GHZ;
		}
		
		public static function isWpaMethodWithPsk($securityMethod)
		{
			$wpaMethods = array	(
									WirelessSecurity::WPA_PSK,
									WirelessSecurity::WPA2_PSK
								);
								
			return in_array($securityMethod, $wpaMethods);
		}
		
		public static function isValidIPTablesTable($table)
		{
			return in_array($table, array("filter", "nat", "mangle"));
		}
		
		public static function getInterfaces()
		{
			return array_keys((array)SystemProfile::getProfile()->interfaces);
		}
		
		public static function isValidIpState(array $states)
		{
			foreach ($states as $state)
			{
				if (!in_array($state, self::$IP_STATES))
					return false;
			}
			
			return true;
		}
		
		public static function isInterfaceUsed($interface)
		{
			if (empty($interface))
				return false;

			$selectHash = new DataHash("interfaces");
			
			foreach ($selectHash->executeSelect() as $resultHash)
			{
				if ($resultHash->getAttribute("physical_int") == $interface)
					return true;
					
				foreach (explode(",", $resultHash->getAttribute("bridged_ints")) as $bridgedInt)
				{
					if ($bridgedInt == $interface)
						return true;
				}
			}
			
			return false;
		}
		
		public static function isInterfaceNameUsed($name)
		{
			if (empty($name))
				return false;
				
			$selectHash = new DataHash("interfaces");
			
			foreach ($selectHash->executeSelect() as $resultHash)
			{
				if ($resultHash->getAttribute("name") == $name)
					return true;
			}
			
			return false;
		}
	}
?>