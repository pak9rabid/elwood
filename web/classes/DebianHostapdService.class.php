<?php
	require_once "Service.class.php";
	require_once "WirelessService.class.php";
	require_once "Console.class.php";
	require_once "RouterSettings.class.php";
	require_once "WirelessSecurity.class.php";
	require_once "FileUtils.class.php";
	
	class DebianHostapdService extends Service implements WirelessService
	{
		private $ssid;
		private $mode;
		private $isSsidHidden;
		private $channel;
		private $securityMethod;
		private $keys = array();
		
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
				throw new Exception("The hostapd service failed to start");
		}
		
		// Override
		public function restart()
		{
			Console::execute("sudo /etc/init.d/hostapd restart");
			
			// hostapd init.d seems to always return an exit status of 0, even if it fails
			// checking if it failed to start this way
			if (!$this->isRunning())
				throw new Exception("The hostapd service failed to retart");
		}
		
		// Override
		public function save()
		{
			$config = array	(
								"interface=" . RouterSettings::getSettingValue("LAN_WLAN"),
								"bridge=" . RouterSettings::getSettingValue("INTIF"),
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
								"auth_algs=3",
								"eapol_key_index_workaround=0",
							);
							
			switch ($this->securityMethod)
			{
				case WirelessSecurity::NONE:
					// no security...nothing else to do
					break;
				case WirelessSecurity::WEP:
					$config[] = "wep_default_key=0";
					
					foreach ($this->keys as $key => $value)
						$config[] = "wep_key$key=$value";
						
					break;
				case WirelessSecurity::WPA_PSK:
					$config[] = "wpa=1";
					$config[] = "wpa_passphrase=" . $this->keys[0];
					$config[] = "wpa_key_mgmt=WPA-PSK";
					$config[] = "wpa_pairwise=TKIP CCMP";
					break;
				case WirelessSecurity::WPA_EAP:
					// TODO: implement this later
					break;
				case WirelessSecurity::WPA2_PSK:
					$config[] = "wpa=2";
					$config[] = "wpa_passphrase=" . $this->keys[0];
					$config[] = "wpa_key_mgmt=WPA-PSK";
					$config[] = "wpa_pairwise=CCMP";
					break;
				case WirelessSecurity::WPA2_EAP:
					// TODO: implement this later
					break;
			}
			
			FileUtils::writeToFile($this->service->config, implode("\n",$config));
		}
		
		// Override
		public function load()
		{
			$config = FileUtils::readFileAsArray($this->service->config);
			$wpaVersion = "";
			$wpaMethod = "";
			
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
						$this->securityMethod = WirelessSecurity::WEP;
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
						$this->keys[0] = $optionValue;
						break;
					case "wep_key1":
						$this->keys[1] = $optionValue;
						break;
					case "wep_key2":
						$this->keys[2] = $optionValue;
						break;
					case "wep_key3":
						$this->keys[3] = $optionValue;
						break;
					case "wpa_passphrase":
						$this->keys = array($optionValue);
						break;
				}
			}
			
			if (empty($this->securityMethod))
			{
				if (!empty($wpaVersion) && !empty($wpaMethod))
				{
					$securityMethod = $wpaVersion . "_" . $wpaMethod;
					eval("\$this->securityMethod = WirelessSecurity::$securityMethod;");
				}
				else
					$this->securityMethod = WirelessSecurity::NONE;
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
		public function	getKeys()
		{
			return $this->keys;
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
		public function setKeys(array $keys)
		{
			if (empty($this->securityMethod) || $this->securityMethod == WirelessSecurity::NONE)
				throw new Exception("A wireless security method must be specified before keys can be set");
								
			$temp = array();
			
			foreach ($keys as $key)
			{
				if (NetUtils::isWpaMethodWithPsk($this->securityMethod) && count($temp) == 1)
					// WPA_PSK only uses a single passphrase
					break;
					
				if ($this->securityMethod == WirelessSecurity::WEP && count($temp) == 4)
					// WEP allows a max of 4 keys to be set
					break;
				
				if (empty($key))
					continue;
					
				if (!NetUtils::isValidWirelessKey($key, $this->securityMethod))
					throw new Exception("Invalid wireless key specified, or the wireless method set doesn't take keys");
					
				$temp[] = $key;
			}
			
			$this->keys = $temp;
		}
	}
?>