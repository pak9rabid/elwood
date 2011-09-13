<?php
	require_once "FirewallRule.class.php";
	require_once "Database.class.php";
	require_once "NetUtils.class.php";
	require_once "Console.class.php";
	require_once "Firewall.class.php";
	
	class FirewallChain
	{
		protected $table;
		protected $chain;
		protected $rules = array();
		
		public function __construct($table = "", $chain = "")
		{
			$this->setTable($table);
			$this->setChain($chain);
		}
		
		public static function getPolicies($table = "")
		{
			// default chains & policies
			$policies = array	(
									"filter.INPUT" => "DROP",
									"filter.OUTPUT" => "ACCEPT",
									"filter.FORWARD" => "DROP",
									"filter.forward_in" => "-",
									"filter.forward_out" => "-",
			
									"nat.PREROUTING" => "ACCEPT",
									"nat.INPUT" => "ACCEPT",
									"nat.OUTPUT" => "ACCEPT",
									"nat.POSTROUTING" => "ACCEPT",
									"nat.one2one_in" => "-",
									"nat.one2one_out" => "-",
									"nat.port_forward" => "-",
									"nat.ip_masquerade" => "-",
								
									"mangle.PREROUTING" => "ACCEPT",
									"mangle.INPUT" => "ACCEPT",
									"mangle.FORWARD" => "ACCEPT",
									"mangle.OUTPUT" => "ACCEPT",
									"mangle.POSTROUTING" => "ACCEPT"
								);
								
			if (empty($table))
				return $policies;
				
			$returnArr = array();
			
			foreach ($policies as $chain => $policy)
			{
				list($tableName, $chainName) = explode(".", $chain);
				
				if ($tableName == $table)
					$returnArr[$chain] = $policy;
			}
			
			return $returnArr;
		}
				
		public function getTable()
		{
			return $this->table;
		}
		
		public function getChainName()
		{
			return $this->chain;
		}
				
		public function getRules()
		{
			return $this->rules;
		}
		
		public function setTable($table)
		{
			if (!NetUtils::isValidIPTablesTable($table))
				throw new Exception("Invalid table specified");
				
			$this->table = $table;
		}
		
		public function setChain($chain)
		{
			if (empty($chain))
				throw new Exception("Chain name not specified");
				
			$this->chain = $chain;
		}
		
		public function clear()
		{
			$this->rules = array();
		}
		
		public function add(FirewallRule $rule)
		{
			// clone $rule, since all PHP5 object params get passed by reference
			// this will allow re-use of a single FirewallRule object when adding multiple rules
			$ruleToAdd  = clone $rule;
			
			// initialize rule
			$ruleToAdd->setAttribute("table_name", $this->table);
			$ruleToAdd->setAttribute("chain_name", $this->chain);
			$ruleToAdd->removeAttribute("id");
			$ruleToAdd->removeAttribute("rule_number");
			
			$this->rules[] = $ruleToAdd;
		}
		
		public function addRulesForService(Service $service)
		{
			foreach ($service->getAccessRules() as $rule)
				$this->add($rule);
		}
		
		// load rules from the database
		public function load()
		{
			if (!self::isInitialized())
				throw new Exception("Table name or chain name not specified");
			
			$selectHash = new FirewallRule();
			$selectHash->setAttribute("table_name", $this->table);
			$selectHash->setAttribute("chain_name", $this->chain);
			$selectHash->setOrderBy(array("rule_number"));
			
			$this->rules = $selectHash->executeSelect();
		}
		
		public function save()
		{
			// save loaded rules into the database
			if (!self::isInitialized())
				throw new Exception("Table name or chain name not specified");
			
			$db = new Database();
			
			// clear out old rules for this chain
			$deleteHash = new FirewallRule();
			$deleteHash->setAttribute("table_name", $this->table);
			$deleteHash->setAttribute("chain_name", $this->chain);
			
			$db->getPdo()->beginTransaction();
			
			try
			{
				$db->executeDelete($deleteHash);
				
				foreach ($this->rules as $rule)
					$db->executeInsert($rule);
			}
			catch(Exception $ex)
			{
				$db->getPdo()->rollBack();
				throw $ex;
			}
			
			$db->getPdo()->commit();
		}
		
		public function apply()
		{
			// apply rules stored in the database to the system firewall
			
			// note: this applies the rules for the entire table that this
			// class resides in, not just the rules in this chain
			Firewall::applyRulesInDatabase($this->table);
		}
		
		protected function isInitialized()
		{
			if (empty($this->table))
				return false;
				
			if (empty($this->chain))
				return false;
				
			return true;
		}
	}
?>
