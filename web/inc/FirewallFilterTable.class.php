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
			 		"	<tr class=\"$policyClass\" nodrag nodrop><th colspan=\"5\">" . ($direction == "in" ? "Incoming" : "Outgoing") . "Traffic</th></tr>\n" .
				 	"	<tr class=\"$policyClass\" nodrag nodrop><th>Proto</th><th>Source</th><th>Port</th><th>Destination</th><th>Port</th></tr>\n";
		
			if (!empty($rules))
			{
				foreach ($rules as $rule)
				{
					$rowClass = $rule->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop";
					$proto = $this->getRuleAttrDisp($rule, "protocol");
					$srcAddr = $this->getRuleAttrDisp($rule, "src_addr") != "*" ? $this->getRuleAttrDisp($rule, "src_addr") : "*";
					$srcPort = $this->getRuleAttrDisp($rule, "sport");
					$dstAddr = $this->getRuleAttrDisp($rule, "dst_addr") != "*" ? $this->getRuleAttrDisp($rule, "dst_addr") : "*";
					$dstPort = $this->getRuleAttrDisp($rule, "dport");
					$ruleId = $this->getRuleAttrDisp($rule, "id");
				
					$out .=	"<tr id=\"$ruleId\" class=\"$rowClass\" onMouseOver=\"showRule(event, this, $ruleId)\" " .
							"onMouseOut=\"hideRule($ruleId)\" onDblClick=\"addEditFilterRuleDlg($ruleId)\"><td>$proto</td><td>$srcAddr</td><td>$srcPort</td>" .
							"<td>$dstAddr</td><td>$dstPort</td></tr>\n";
				
					// Create div to store rule details
					$ruleDivs .= "<div id=\"" . $ruleId . "details\" class=\"fwRuleDetails\">\n" .
								 "	<table class=\"fwDetailsTable\">\n" .
								 "		<tr><td class=\"label\">Protocol:</td><td>$proto</td></tr>\n" .
								 "		<tr><td class=\"label\">Source Address:</td><td>$srcAddr</td></tr>\n" .
								 "		<tr><td class=\"label\">Source Port:</td><td>$srcPort</td></tr>\n" .
								 "		<tr><td class=\"label\">Destination Address:</td><td>$dstAddr</td></tr>\n" .
								 "		<tr><td class=\"label\">Destination Port:</td><td>$dstPort</td></tr>\n" .
								 "		<tr><td class=\"label\">States:</td><td>" . $this->getRuleAttrDisp($rule, "state") . "</td></tr>\n" .
								 "		<tr><td class=\"label\">Fragmented:</td><td>" . $this->getRuleAttrDisp($rule, "fragmented") . "</td></tr>\n";
				
					if ($proto == "icmp")
						$ruleDivs .= "		<tr><td class=\"label\">ICMP Type:</td><td>" . $this->getRuleAttrDisp($rule, "icmp_type") . "</td></tr>\n";
					
					$ruleDivs .= "		<tr><td class=\"label\">Target:</td><td>" . $this->getRuleAttrDisp($rule, "target") . "</td></tr>\n" .
								 "	</table>\n" .
								 "</div>\n";
				}
			}
			else
				$out .= "	<tr nodrag nodrop><td colspan=\"5\">None</td><tr>\n";
		
			$out .= "</table>\n";
			$out .= $ruleDivs;
			
			return $out;
		}
		
		private function getRuleAttrDisp(FirewallFilterRule $rule, $attribute)
		{
			return $rule->getAttribute($attribute) == null ? "*" : $rule->getAttribute($attribute);
		}
	}
?>