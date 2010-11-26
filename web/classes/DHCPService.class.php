<?php
	interface DHCPService
	{
		public function getIpRanges();
		public function getStickyIps();
		public function getDomain();
		public function getNameservers();
		public function setIpRanges(array $ipRanges);
		public function setStickyIps(array $stickyIps);
		public function setDomain($domain);
		public function setNameservers(array $nameservers);
	}
?>