<?php
	require_once "DataHash.class.php";

	class FirewallFilterRule extends DataHash
	{
		public function __construct()
		{
			parent::__construct("firewall_filter_rules");
		}
		
		public function toJson($isDetail = false)
		{
			return json_encode($this->hashMap);
		}
	}
?>
