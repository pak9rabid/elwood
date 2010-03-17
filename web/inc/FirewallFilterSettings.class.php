<?php
	require_once "TempDatabase.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "DbQueryPreper.class.php";
	
	class FirewallFilterSettings
	{
		public static function getChain($chainName)
		{
			// Returns specified chain
			$prep = new DbQueryPreper("SELECT * FROM firewall_chains WHERE chain_name = ");
			$prep->addVariable($chainName);
			
			try
			{
				$result = TempDatabase::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			if (count($result) <= 0)
				throw new Exception("Specified chain does not exist");
				
			$chain = new FirewallChain();
			$chain->setAllAttributes($result[0]);
			
			return $chain;
		}
		
		public static function getChains()
		{
			// Returns an array of chains from the filter table
			$chains = array();
			$prep = new DbQueryPreper("SELECT * FROM firewall_chains");
			
			try
			{
				$results = TempDatabase::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			foreach ($results as $row)
			{
				$chain = new FirewallChain();
				$chain->setAllAttributes($row);
				$chains[] = $chain;
			}
			
			return $chains;
		}
		
		public static function getRule($id)
		{
			// Returns the specified filter firewall rule
			$prep = new DbQueryPreper("SELECT * FROM firewall_filter_rules " .
									  "WHERE id = ");
			$prep->addVariable($id);
			
			try
			{
				$result = TempDatabase::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			if (count($result) <= 0)
				throw new Exception("Specified firewall rule does not exist");
				
			$rule = new FirewallFilterRule();
			$rule->setAllAttributes($result[0]);
			
			return $rule;
		}
		
		public static function getRules($chain)
		{
			// Returns an array of rules for the specified chain
			$rules = array();
			$prep = new DbQueryPreper("SELECT * FROM firewall_filter_rules " .
									  "WHERE chain_name = ");
			$prep->addVariable($chain);
			$prep->addSql(" ORDER BY rule_number");
			
			try
			{
				$results = TempDatabase::executeQuery($prep);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			foreach ($results as $row)
			{
				$rule = new FirewallFilterRule();
				$rule->setAllAttributes($row);
				$rules[] = $rule;
			}
			
			return $rules;
		}
		
		public static function orderRules(Array $ruleIds)
		{
			// Orders the firewall rules by the given order specified in
			// the $ruleIds list
			
			$rules = array();
			
			// Clear existing rules from the temp database
			foreach ($ruleIds as $ruleId)
			{
				$rule = self::getRule($ruleId);
				$rule->executeDelete(true);
				$rules[] = $rule;
			}
			
			// Set the rule number attribute to match the order in which
			// the rules were specified
			foreach ($rules as $key => $rule)
			{
				$rule->setAttribute("rule_number", $key);
				$rule->executeInsert(true);
			}
		}
	}
?>