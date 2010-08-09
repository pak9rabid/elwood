<?php
	require_once "DataHash.class.php";
	
	class IPTablesPortforwardRule extends DataHash
	{
		public function __construct()
		{
			parent::__construct("firewall_dnat_rules");
		}
	}
?>