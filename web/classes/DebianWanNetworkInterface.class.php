<?php
	require_once "DebianNetworkInterface.class.php";
	require_once "RouterSettings.class.php";
	
	class DebianWanNetworkInterface extends DebianNetworkInterface
	{
		public function __construct()
		{
			$this->name = RouterSettings::getSettingValue("EXTIF");
		}
	}
?>