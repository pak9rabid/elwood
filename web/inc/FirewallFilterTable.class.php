<?php
	require_once "FirewallFilterSettings.class.php";
	
	class FirewallFilterTable
	{
		// Attributes
		protected $chains = array();
		protected $rules = array();
		
		// Constructors
		public function __construct()
		{
			// Initialize with rules stored in the database
			foreach (FirewallFilterSettings::getChains() as $chain)
			{
				$chainName = $chain->getAttribute("chain_name");
				$this->chains[$chainName] = $chain;
				$this->rules[$chainName] = FirewallFilterSettings::getRules($chainName);
			}
		}
		
		// Methods
		public function getChain($chain)
		{
			return $this->chains[$chain];
		}
		
		public function getChains()
		{
			return $this->chains;
		}
		
		public function getRules($chain)
		{
			return $this->rules[$chain];
		}
	}
?>