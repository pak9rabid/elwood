<?php
	require_once "DebianNetworkInterface.class.php";
	require_once "RouterSettings.class.php";

	class DebianLanNetworkInterface extends DebianNetworkInterface
	{
		private $ethInt;
		private $wlanInt;
		
		public function __construct()
		{
			$this->name = RouterSettings::getSettingValue("INTIF");			
			$this->ethInt = RouterSettings::getSettingValue("LAN_ETH");
			$this->wlanInt = RouterSettings::getSettingValue("LAN_WLAN");
		}
		
		// Override
		public function generateConfig()
		{			
			$out = parent::generateConfig();
			$out[] = "bridge_ports " . $this->ethInt . (!empty($this->wlanInt) ? " " . $this->wlanInt : "");
			$out[] = "bridge_maxwait 0";
			
			return $out;
		}
	}
?>