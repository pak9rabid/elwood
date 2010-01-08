<?php
	require_once "FirewallSettings.class.php";

	class FirewallTable
	{
		// Attributes
		private $tableName;
		private $firewallChains = array();
		private $firewallRules = array();

		// Constructors
		public function __construct($tableName)
		{
			$this->tableName = $tableName;

			# Query database for existing rules for table $tableName
			try
			{
				$this->firewallChains = FirewallSettings::getChains($this->tableName);

				foreach ($this->firewallChains as $chain)
					$this->firewallRules = array_merge($this->firewallRules, FirewallSettings::getRules($this->tableName, $chain->getAttribute("CHAIN_NAME")));
			}
			catch (Exception $ex)
			{
				// Clear chains and rules arrays
				$this->firewallChyains = array();
				$this->firewallRules = array();
			}
		}
	}
?>
