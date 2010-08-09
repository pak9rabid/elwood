<?php
	require_once "DataHash.class.php";

	class FirewallChain extends DataHash
	{
		// Constructors
		public function __construct()
		{
			parent::__construct("firewall_chains");
		}
	}
?>
