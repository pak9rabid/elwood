<?php
	interface WirelessService
	{
		public function isSsidHidden();
		public function getSsid();
		public function getMode();
		public function getChannel();
		public function getSecurityMethod();
		public function	getWepKeys();
		public function getWpaPassphrase();
		public function getAuthMethod();
		public function getDefaultWepKeyIndex();
		public function getAuthServerAddr();
		public function getAuthServerPort();
		public function getAuthServerSharedSecret();
		public function getAcctServerAddr();
		public function getAcctServerPort();
		public function getAcctServerSharedSecret();
		public function setHideSsid($isHidden);
		public function setSsid($ssid);
		public function setMode($mode);
		public function setChannel($channel);
		public function setSecurityMethod($method);
		public function setWepKeys(array $keys);
		public function setWpaPassphrase($passphrase);
		public function setAuthMethod($authMethod);
		public function setDefaultWepKeyIndex($index);
		public function setAuthServerAddr($addr);
		public function setAuthServerPort($port);
		public function setAuthServerSharedSecret($secret);
		public function setAcctServerAddr($addr);
		public function setAcctServerPort($port);
		public function setAcctServerSharedSecret($secret);
	}
?>