<?php
	interface WirelessService
	{
		public function isSsidHidden();
		public function getSsid();
		public function getMode();
		public function getChannel();
		public function getSecurityMethod();
		public function	getKeys();
		public function setHideSsid($isHidden);
		public function setSsid($ssid);
		public function setMode($mode);
		public function setChannel($channel);
		public function setSecurityMethod($method);
		public function setKeys(array $keys);
	}
?>