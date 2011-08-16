<?php
	require_once "Service.class.php";
	require_once "WirelessService.class.php";
	require_once "Console.class.php";
	require_once "RouterSettings.class.php";
	require_once "WirelessSecurity.class.php";
	require_once "FileUtils.class.php";
	require_once "NetworkInterface.class.php";
	
	class DebianHostapdService extends Service implements WirelessService
	{
		private $ssid;
		private $mode = "b";
		private $isSsidHidden;
		private $channel = 1;
		private $securityMethod;
		private $wpaPassphrase;
		private $wepKeys = array();
		private $authMethod = WirelessSecurity::AUTH_OPEN;
		private $defaultWepKeyIndex = 0;
		private $authServerAddr;
		private $authServerPort;
		private $authServerSharedSecret;
		private $acctServerAddr;
		private $acctServerPort;
		private $acctServerSharedSecret;
		
		public function __construct()
		{
			parent::__construct();
		}
		
		// Override
		public function stop()
		{
			if ($this->isRunning())
			{
				Console::execute("sudo /etc/init.d/hostapd stop");
				sleep(3);
			}
		}
		
		// Override
		public function start()
		{
			if ($this->isRunning())
				return;
				
			Console::execute("sudo /etc/init.d/hostapd start");
			sleep(3);
			
			// hostapd init.d seems to always return an exit status of 0, even if it fails
			// checking if it failed to start this way
			if (!$this->isRunning())
			{
				$this->setAttribute("is_enabled", "N");
				$this->save();
				throw new Exception("The hostapd service failed to start");
			}
		}
		
		// Override
		public function restart()
		{
			Console::execute("sudo /etc/init.d/hostapd restart");
			
			// hostapd init.d seems to always return an exit status of 0, even if it fails
			// checking if it failed to start this way
			if (!$this->isRunning())
			{
				$this->setAttribute("is_enabled", "N");
				$this->save();
				throw new Exception("The hostapd service failed to restart");
			}
		}
		
		// Override
		public function save()
		{
			parent::save();
			
			$config = array	(
								"interface=" . RouterSettings::getSettingValue("AP_INT"),
								$this->isApInterfaceBridged() ? "bridge=" . NetworkInterface::getInstance("LAN")->getPhysicalInterface() : "",
								"driver=nl80211",
								"logger_syslog=-1",
								"logger_syslog_level=2",
								"logger_stdout=-1",
								"logger_stdout_level=2",
								"debug=2",
								"dump_file=/tmp/hostapd.dump",
								"ctrl_interface=/var/run/hostapd",
								"ctrl_interface_group=0",
								"ssid=" . $this->ssid,
								"hw_mode=" . $this->mode,
								"channel=" . $this->channel,
								"ignore_broadcast_ssid=" . ($this->isSsidHidden ? "1" : "0"),
								"eapol_key_index_workaround=0",
							);

			if ($this->securityMethod == WirelessSecurity::WEP)
			{
				$config[] = "auth_algs=" . $this->authMethod;
				$config[] = "wep_default_key=" . $this->defaultWepKeyIndex;
				
				foreach ($this->wepKeys as $key => $value)
					$config[] = "wep_key$key=$value";
			}
			else
			{
				if ($this->securityMethod != WirelessSecurity::NONE)
					$config[] = "auth_algs=1";
					
				if	(in_array($this->securityMethod, array	(
																WirelessSecurity::WPA_EAP,
																WirelessSecurity::WPA_PSK
															)))
				{
					$config[] = "wpa=1";
					$config[] = "wpa_pairwise=TKIP";
				}
				else if (in_array($this->securityMethod, array	(
																WirelessSecurity::WPA2_EAP,
																WirelessSecurity::WPA2_PSK
															)))
				{
					$config[] = "wpa=2";
					$config[] = "rsn_pairwise=CCMP";
					$config[] = "auth_algs=1";
				}
				
				if (in_array($this->securityMethod, array	(
																WirelessSecurity::WPA_PSK,
																WirelessSEcurity::WPA2_PSK
															)))
				{
					$config[] = "wpa_passphrase=" . $this->wpaPassphrase;
					$config[] = "wpa_key_mgmt=WPA-PSK";
				}
				else if (in_array($this->securityMethod, array	(
																WirelessSecurity::WPA_EAP,
																WirelessSecurity::WPA2_EAP
															)))
				{
					$config[] = "own_ip_addr=" . NetworkInterface::getInstance("LAN")->getIp();
					$config[] = "ieee8021x=1";
					$config[] = "auth_server_addr=" . $this->authServerAddr;
					$config[] = "auth_server_port=" . $this->authServerPort;
					$config[] = "auth_server_shared_secret=" . $this->authServerSharedSecret;
					$config[] = "acct_server_addr=" . $this->acctServerAddr;
					$config[] = "acct_server_port=" . $this->acctServerPort;
					$config[] = "acct_server_shared_secret=" . $this->acctServerSharedSecret;
					$config[] = "wpa_key_mgmt=WPA-EAP";
				}
			}
			
			FileUtils::writeToFile($this->service->config, implode("\n",$config));
		}
		
		// Override
		public function load()
		{
			parent::load();
			
			$config = FileUtils::readFileAsArray($this->service->config);
			$wpaVersion = "";
			$wpaMethod = "";
			$wepKeys = array();
			$wepDefaultKeyIndex = "";
			
			foreach ($config as $line)
			{
				list ($optionName, $optionValue) = explode("=", $line);
				$optionName = trim($optionName);
				$optionValue = trim($optionValue);
								
				switch ($optionName)
				{
					case "ssid":
						$this->setSsid($optionValue);
						break;
					case "ignore_broadcast_ssid":
						$this->isSsidHidden = $optionValue != 0;
						break;
					case "hw_mode":
						$this->setMode($optionValue);
						break;
					case "channel":
						$this->setChannel($optionValue);
						break;
					case "wep_default_key":
						$this->setSecurityMethod(WirelessSecurity::WEP);
						$wepDefaultKeyIndex = $optionValue;
						break;
					case "wpa":
						
						if ($optionValue == 1)
							$wpaVersion = "WPA";
						else
							$wpaVersion = "WPA2";
							
						break;
					case "wpa_key_mgmt":
						list($temp, $wpaMethod) = explode("-", $optionValue);
						break;
					case "wep_key0":
						$wepKeys[0] = $optionValue;
						break;
					case "wep_key1":
						$wepKeys[1] = $optionValue;
						break;
					case "wep_key2":
						$wepKeys[2] = $optionValue;
						break;
					case "wep_key3":
						$wepKeys[3] = $optionValue;
						break;
					case "wpa_passphrase":
						$this->setWpaPassphrase($optionValue);
						break;
					case "auth_algs":
						$this->setAuthMethod($optionValue);
						break;
					case "auth_server_addr":
						$this->setAuthServerAddr($optionValue);
						break;
					case "auth_server_port":
						$this->setAuthServerPort($optionValue);
						break;
					case "auth_server_shared_secret":
						$this->setAuthServerSharedSecret($optionValue);
						break;
					case "acct_server_addr":
						$this->setAcctServerAddr($optionValue);
						break;
					case "acct_server_port":
						$this->setAcctServerPort($optionValue);
						break;
					case "acct_server_shared_secret":
						$this->setAcctServerSharedSecret($optionValue);
						break;
				}
			}
			
			if (!empty($wepKeys))
				$this->setWepKeys($wepKeys);
				
			if (!empty($wepDefaultKeyIndex))
				$this->setDefaultWepKeyIndex($wepDefaultKeyIndex);
			
			if (empty($this->securityMethod))
			{
				if (!empty($wpaVersion) && !empty($wpaMethod))
				{
					$securityMethod = $wpaVersion . "_" . $wpaMethod;
					
					switch ($securityMethod)
					{
						case "WPA_PSK":
							$this->setSecurityMethod(WirelessSecurity::WPA_PSK);
							break;
							
						case "WPA_EAP":
							$this->setSecurityMethod(WirelessSecurity::WPA_EAP);
							break;
							
						case "WPA2_PSK":
							$this->setSecurityMethod(WirelessSecurity::WPA2_PSK);
							break;
							
						case "WPA2_EAP":
							$this->setSecurityMethod(WirelessSecurity::WPA2_EAP);
							break;
					}
				}
				else
					$this->setSecurityMethod(WirelessSecurity::NONE);
			}
		}
		
		// Override
		public function isRunning()
		{
			return file_exists($this->service->pid);
		}
		
		// Override
		public function isSsidHidden()
		{
			return $this->isSsidHidden;
		}
		
		// Override
		public function getSsid()
		{
			return $this->ssid;
		}
				
		// Override
		public function getMode()
		{
			return $this->mode;
		}
		
		// Override
		public function getChannel()
		{
			return $this->channel;
		}
		
		// Override
		public function getSecurityMethod()
		{
			return $this->securityMethod;
		}
		
		// Override
		public function	getWepKeys()
		{
			return $this->wepKeys;
		}
		
		// Override
		public function getWpaPassphrase()
		{
			return $this->wpaPassphrase;
		}
		
		// Override
		public function getAuthMethod()
		{
			return $this->authMethod;
		}
		
		// Override
		public function getDefaultWepKeyIndex()
		{
			return $this->defaultWepKeyIndex;
		}
		
		// Override
		public function getAuthServerAddr()
		{
			return $this->authServerAddr;
		}
		
		// Override
		public function getAuthServerPort()
		{
			return $this->authServerPort;
		}
		
		// Override
		public function getAuthServerSharedSecret()
		{
			return $this->authServerSharedSecret;
		}
		
		// Override
		public function getAcctServerAddr()
		{
			return $this->acctServerAddr;
		}
		
		// Override
		public function getAcctServerPort()
		{
			return $this->acctServerPort;
		}
		
		// Override
		public function getAcctServerSharedSecret()
		{
			return $this->acctServerSharedSecret;
		}
				
		// Override
		public function setHideSsid($isHidden)
		{
			$this->isSsidHidden = (bool) $isHidden;
		}
		
		// Override
		public function setSsid($ssid)
		{
			if (empty($ssid))
				throw new Exception("Empty ssid specified");
				
			if (!NetUtils::isValidSsid($ssid))
				throw new Exception("Invalid ssid specified");
				
			$this->ssid = $ssid;
		}
		
		// Override
		public function setMode($mode)
		{
			if (!NetUtils::isValidWirelessMode($mode))
				throw new Exception("Invalid wireless mode specified");
				
			$this->mode = $mode;
		}
		
		// Override
		public function setChannel($channel)
		{
			if (empty($this->mode))
				throw new Exception("Wireless mode must be specified before a channel can be set");
				
			if (!NetUtils::isValidWirelessChannel($channel, $this->mode))
				throw new Exception("Invalid wireless channel specified for mode " . $this->mode);
				
			$this->channel = $channel;
		}

		// Override
		public function setSecurityMethod($method)
		{
			if (!NetUtils::isValidWirelessSecurityMethod($method))
				throw new Exception("Invalid security method specified");
				
			$this->securityMethod = $method;
		}
		
		// Override
		public function setWepKeys(array $wepKeys)
		{
			$temp = array();
			
			foreach ($wepKeys as $key)
			{
				if (count($temp) == 4)
					// Max of 4 keys accepted..others are simply dropped
					break;
					
				if (empty($key))
					continue;
					
				if (!NetUtils::isValidWirelessKey($key, WirelessSecurity::WEP))
					throw new Exception("Invalid WEP key specified");
					
				$temp[] = $key;
			}
			
			$this->wepKeys = $temp;
		}
		
		// Override
		public function setWpaPassphrase($passphrase)
		{
			if (!NetUtils::isValidWirelessKey($passphrase, WirelessSecurity::WPA_PSK))
				throw new Exception ("Invalid WPA passphrase specified");
				
			$this->wpaPassphrase = $passphrase;
		}
		
		// Override
		public function setAuthMethod($authMethod)
		{
			if (!NetUtils::isValidWirelessAuthMethod($authMethod))
				throw new Exception("Invalid wireless authentication method specified");
				
			$this->authMethod = $authMethod;
		}
		
		// Override
		public function setDefaultWepKeyIndex($index)
		{
			// $index can be 0-3, and must be less than the count of the wep keys set
			if (!preg_match("/^[0-3]$/", $index) || $index >= count($this->wepKeys))
				throw new Exception("Invalid default key index specified");
				
			$this->defaultWepKeyIndex = $index;
		}
		
		// Override
		public function setAuthServerAddr($addr)
		{
			if (empty($addr) || !NetUtils::isValidIp($addr))
				throw new Exception("Invalid IP address specified for authentication server");
				
			$this->authServerAddr = $addr;
		}
		
		// Override
		public function setAuthServerPort($port)
		{
			if (empty($port) || !NetUtils::isValidIanaPortNumber($port))
				throw new Exception("Invalid port number specified for authentication server");
				
			$this->authServerPort = $port;
		}
		
		// Override
		public function setAuthServerSharedSecret($secret)
		{
			if (empty($secret) || !NetUtils::isValidWirelessKey($secret, WirelessSecurity::WPA_PSK))
				throw new Exception("Invalid shared secret entered for authentication server");
				
			$this->authServerSharedSecret = $secret;
		}
		
		// Override
		public function setAcctServerAddr($addr)
		{
			if (empty($addr) || !NetUtils::isValidIp($addr))
				throw new Exception("Invalid IP address specified");
				
			$this->acctServerAddr = $addr;
		}
		
		// Override
		public function setAcctServerPort($port)
		{
			if (empty($port) || !NetUtils::isValidIanaPortNumber($port))
				throw new Exception("Invalid port number specified for accounting server");
				
			$this->acctServerPort = $port;
		}
		
		// Override
		public function setAcctServerSharedSecret($secret)
		{
			if (empty($secret) || !NetUtils::isValidWirelessKey($secret, WirelessSecurity::WPA_PSK))
				throw new Exception("Invalid shared secret entered for accounting server");
				
			$this->acctServerSharedSecret = $secret;
		}
		
		// Override
		public function getDefaultAccessRules()
		{
			return array();
		}
		
		private function isApInterfaceBridged()
		{
			$apInterface = RouterSettings::getSettingValue("AP_INT");
						
			return empty($apInterface) ? false : in_array($apInterface, NetworkInterface::getInstance("LAN")->getBridgedInterfaces());
		}
	}
?>