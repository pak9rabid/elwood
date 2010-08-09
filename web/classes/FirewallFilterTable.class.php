<?php
	require_once "FirewallFilterSettings.class.php";
	require_once"NetUtils.class.php";
	
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
				
		public function out($direction)
		{
			$policy = $this->getChain("FORWARD")->getAttribute("policy");
			$rules = $direction == "in" ? $this->getRules("forward_in") : $this->getRules("forward_out");		
			$policyClass = $policy == "ACCEPT" ? "fwPolicyAccept" : "fwPolicyDrop";
			$out = "";
			$ruleDivs = "";

			$out =  "<table id=\"firewall-table\">\n" .
			 		"	<tr class=\"$policyClass nodrag nodrop\">\n" .
					"		<th colspan=\"5\">" .
								($direction == "in" ? "Incoming" : "Outgoing") . "Traffic" .
					"		</th>\n" .
					"	</tr>\n" .
				 	"	<tr class=\"$policyClass nodrag nodrop\">\n" .
					"		<th class=\"firewall-table-protocol-col\">Proto</th>\n" .
					"		<th class=\"firewall-table-address-col\">Source</th>\n" .
					"		<th class=\"firewall-table-port-col\">Port</th>\n" .
					"		<th class=\"firewall-table-address-col\">Destination</th>\n" .
					"		<th class=\"firewall-table-port-col\">Port</th>\n" .
					"	</tr>\n";
		
			if (!empty($rules))
			{
				foreach ($rules as $rule)
				{
					$ruleHtml = $rule->out();
					$out .= $ruleHtml->row;
					$ruleDivs .= $ruleHtml->div;
				}
			}
		
			$out .= "</table>\n";
			$out .= $ruleDivs;
			
			return $out;
		}
	}
?>