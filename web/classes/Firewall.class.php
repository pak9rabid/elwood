<?php
	require_once "FirewallRule.class.php";
	require_once "FirewallChain.class.php";
	require_once "Console.class.php";
	require_once "NetUtils.class.php";
	
	class Firewall
	{
		public static function applyRulesInDatabase($tableName)
		{
			if (!NetUtils::isValidIPTablesTable($tableName))
				throw new Exception("Invalid table specified");
				
			$selectHash = new FirewallRule();
			$selectHash->setAttribute("table_name", $tableName);
			$selectHash->setOrderBy(array("chain_name", "rule_number"));
			$chainRules = $selectHash->executeSelect();
			
			$chains = array();
			$rules = array();
			
			foreach (FirewallChain::getPolicies($tableName) as $chain => $policy)
			{
				list($table, $chain) = explode(".", $chain);
				$chains[$chain] = $policy;
			}
			
			foreach ($chainRules as $rule)
			{
				$chainName = $rule->getAttribute("chain_name");
							
				if (!in_array($chainName, array_keys($chains)))
					$chains[$chainName] = "-";
					
				$rules[] = $rule->toIPTablesRule();
			}
						
			$iptablesRestore = array("*" . $tableName);
			
			foreach ($chains as $chain => $policy)
				$iptablesRestore[] = ":$chain $policy";
				
			$iptablesRestore = array_merge($iptablesRestore, $rules);	
			$iptablesRestore[] = "COMMIT";
						
			Console::execute("echo \"" . implode("\n", $iptablesRestore) . "\" | sudo /sbin/iptables-restore");
		}
	}
?>