<?php
	require_once "Database.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	
	class FirewallFilterSettings
	{
		public static function getChain($chainName)
		{
			// Returns specified chain
			$query = "SELECT * " .
					 "FROM firewall_chains " .
					 "WHERE chain_name = '$chainName'";
			
			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			if (sqlite_num_rows($result) <= 0)
				throw new Exception("Specified chain does not exist");
				
			$row = sqlite_fetch_array($result, SQLITE_ASSOC);
			$chain = new FirewallChain();
			$chain->setAllAttributes($row);
			
			return $chain;
		}
		
		public static function getChains()
		{
			// Returns an array of chains from the filter table
			$chains = array();
			
			$query = "SELECT * " .
					 "FROM firewall_chains";
			
			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			while (($row = sqlite_fetch_array($result, SQLITE_ASSOC)) == true)
			{
				$chain = new FirewallChain();
				$chain->setAllAttributes($row);
				$chains[] = $chain;
			}
			
			return $chains;
		}
		
		public static function getRule($chain, $id)
		{
			// Returns the specified filter firewall rule
			$query = "SELECT * " .
			         "FROM firewall_filter_rules " .
					 "WHERE chain_name = '$chain' AND id = '$id'";
			
			echo $query;
			
			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			if (sqlite_num_rows($result) <= 0)
				throw new Exception("Specified firewall rule does not exist");
				
			$row = sqlite_fetch_array($result, SQLITE_ASSOC);
			$rule = new FirewallFilterRule();
			$rule->setAllAttributes($row);
			
			return $rule;
		}
		
		public static function getRules($chain)
		{
			// Returns an array of rules for the specified chain
			$rules = array();
			
			$query = "SELECT * " .
					 "FROM firewall_filter_rules " .
					 "WHERE chain_name = '$chain' " .
					 "ORDER BY rule_number";
			
			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}
			
			while (($row = sqlite_fetch_array($result, SQLITE_ASSOC)) == true)
			{
				$rule = new FirewallFilterRule();
				$rule->setAllAttributes($row);
				$rules[] = $rule;
			}
			
			return $rules;
		}
	}
?>