<?php
	require_once "Database.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallRule.class.php";

	class FirewallSettings
	{
		// Methods
		public static function getRule($index)
		{
			// Returns specified rule, or false if rule doesn't exist
			$query = "SELECT * " .
				 "FROM firewall_rules " .
				 "WHERE id = $index";

			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			if (sqlite_num_rows($result) <= 0)
				return false;

			$row = sqlite_fetch_array($result, SQLITE_ASSOC);

			$rule = new FirewallRule("firewall_rules");
			$rule->setAttribute("ID", $index);
			$rule->setAttribute("TABLE_NAME", $row['table_name']);
			$rule->setAttribute("CHAIN_NAME", $row['chain_name']);
			$rule->setAttribute("OPERATION", $row['operation']);
			$rule->setAttribute("OPTIONS", $row['options']);

			return $rule;
		}

		public static function getRules($tableName, $chainName)
		{
			// Returns an array of rules for the specified table and chain names
			$rules = array();

			$query = "SELECT * " .
				 "FROM firewall_rules " .
				 "WHERE table_name = '$tableName' AND chain_name = '$chainName'";

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
				$rule = new FirewallRule("firewall_rules");
				$rule->setPrimaryKey("ID");
				$rule->setAttribute("ID", $row['id']);
				$rule->setAttribute("TABLE_NAME", $tableName);
				$rule->setAttribute("CHAIN_NAME", $chainName);
				$rule->setAttribute("OPERATION", $row['operation']);
				$rule->setAttribute("OPTIONS", $row['options']);

				$rules[] = $rule;
			}

			return $rules;
		}

		public static function getChain($tableName, $chainName)
		{
			// Returns specified chain, or false if none exist
			$query = "SELECT * " .
				 "FROM firewall_chains " .
				 "WHERE table_name = '$tableName' AND chain_name = '$chainName'";

			try
			{
				$result = Database::executeQuery($query);
			}
			catch (Exception $ex)
			{
				throw $ex;
			}

			if (sqlite_num_rows($result) <= 0)
				return false;

			$row = sqlite_fetch_array($result, SQLITE_ASSOC);

			$chain = new FirewallChain("firewall_chains");
			$chain->setPrimaryKey("ID");
			$chain->setAttribute("ID", $row['id']);
			$chain->setAttribute("TABLE_NAME", $tableName);
			$chain->setAttribute("CHAIN_NAME", $chainName);
			$chain->setAttribute("POLICY", $row['policy']);

			return $chain;
		}

		public static function getChains($tableName)
		{
			// Return an array of chains for the specified table
			$chains = array();

			$query = "SELECT * " .
				 "FROM firewall_chains " .
				 "WHERE table_name = '$tableName'";

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
				$chain = new FirewallChain("firewall_chains");
				$chain->setPrimaryKey("ID");
				$chain->setAttribute("ID", $row['id']);
				$chain->setAttribute("TABLE_NAME", $tableName);
				$chain->setAttribute("CHAIN_NAME", $row['chain_name']);
				$chain->setAttribute("POLICY", $row['policy']);

				$chains[] = $chain;
			}

			return $chains;
		}
	}
?>
