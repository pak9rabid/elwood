<?php
	require_once "FirewallRule.class.php";
	require_once "Database.class.php";
	
	class FirewallChain
	{
		protected $table;
		protected $chain;
		protected $rules = array();
		
		public function __construct($table, $chain)
		{
			$this->setTable($table);
			$this->setChain($chain);
		}
		
		public function getTable()
		{
			return $this->table;
		}
		
		public function getChain()
		{
			return $this->chain;
		}
				
		public function getRules()
		{
			return $this->rules;
		}
		
		public function setTable($table)
		{
			if (empty($table))
				throw new Exception("Table name not specified");
				
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
			$this->rules[] = $rule;
		}
		
		public function load()
		{
			if (empty($this->table))
				throw new Exception("Table name must be specified to load rules");
				
			if (empty($this->chain))
				throw new Exception("Chain name must be specified to load rules");
			
			$selectHash = new FirewallRule();
			$selectHash->setAttribute("table_name", $this->table);
			$selectHash->setAttribute("chain_name", $this->chain);
			$selectHash->setOrderBy("rule_number");
			
			$this->rules = $selectHash->executeSelect();
		}
		
		public function save()
		{
			if (empty($this->table))
				throw new Exception("Table name must be specified to save rules");
				
			if (empty($this->chain))
				throw new Exception("Chain name must be specified to save rules");
				
			foreach ($this->rules as $rule)
			{
				$rule->setAttribute("table_name", $this->table);
				$rule->setAttribute("chain_name", $this->chain);
			}
			
			// clear out the old rules and replace them with these
			$deleteHash = new FirewallRule();
			$deleteHash->setAttribute("table_name", $this->table);
			$deleteHash->setAttribute("chain_name", $this->chain);
			$deleteHash->executeDelete();
			
			$db = new Database();
			$db->executeInserts($this->rules);
		}
	}
?>
