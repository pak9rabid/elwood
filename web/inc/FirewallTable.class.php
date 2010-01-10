<?php
	require_once "FirewallSettings.class.php";

	class FirewallTable
	{
		// Attributes
		private $tableName;
		private $firewallChains = array();
		private $firewallRules = array();

		// Constructors
		public function __construct($tableName, $queryDb)
		{
			$this->tableName = $tableName;

			if ($queryDb)
			{
				// Query database for existing rules for table $tableName
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

		// Methods
		public function getTableName()
		{
			return $this->tableName;
		}

		public function getFirewallChains()
		{
			return $this->firewallChains;
		}

		public function getFirewallRules()
		{
			return $this->firewallRules;
		}

		public function setFirewallChains(array $chains)
		{
			$this->firewallChains = $chains;
		}

		public function setFirewallRules(array $rules)
		{
			$this->firewallRules = $rules;
		}

		public function commandOut()
		{
			if (empty($this->firewallChains))
				return "";

			$out = "*" . $this->tableName . "\n";

			foreach ($this->firewallChains as $chain)
				$out .= $chain->commandOut() . "\n";

			foreach ($this->firewallRules as $rule)
				$out .= $rule->commandOut() . "\n";

			$out .= "COMMIT";

			return $out;
		}
	}
?>
