<?php
	require_once "DebianNetworkInterface.class.php";
	require_once "RouterSettings.class.php";

	class DebianLanNetworkInterface extends DebianNetworkInterface
	{
		public function __construct()
		{
			$this->name = RouterSettings::getSettingValue("INTIF");
		}
	}
?>