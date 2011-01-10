<?php
	require_once "FirewallRule.class.php";
	require_once "Database.class.php";
	require_once "NetUtils.class.php";
	require_once "Console.class.php";
	
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
			$policies = array	(
									"filter.INPUT" => "DROP",
									"filter.OUTPUT" => "ACCEPT",
									"filter.FORWARD" => "DROP",
			
									"nat.PREROUTING" => "ACCEPT",
									"nat.INPUT" => "ACCEPT",
									"nat.OUTPUT" => "ACCEPT",
									"nat.POSTROUTING" => "ACCEPT",
								
									"mangle.PREROUTING" => "ACCEPT",
									"mangle.INPUT" => "ACCEPT",
									"mangle.FORWARD" => "ACCEPT",
									"mangle.OUTPUT" => "ACCEPT",
									"mangle.POSTROUTING" => "ACCEPT"
								);
								
			if (empty($table))
				return policies;
				
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
			
			$ruleToAdd->setAttribute("table_name", $this->table);
			$ruleToAdd->setAttribute("chain_name", $this->chain);
			$this->rules[] = $ruleToAdd;
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
					
			// clear out the old rules and replace them with these
			$deleteHash = new FirewallRule();
			$deleteHash->setAttribute("table_name", $this->table);
			$deleteHash->setAttribute("chain_name", $this->chain);
			$deleteHash->executeDelete();
			
			$db = new Database();
			$db->executeInserts($this->rules);
		}
		
		public function apply()
		{
			// apply rules stored in the database to the system firewall
			
			// note: this applies the rules for the entire table that this
			// class resides in, not just the rules in this chain
			$selectHash = new FirewallRule();
			$selectHash->setAttribute("table_name", $this->table);
			$selectHash->setOrderBy(array("chain_name", "rule_number"));
			$chainRules = $selectHash->executeSelect();
			
			$chains = array();
			$rules = array();
			
			foreach (self::getPolicies($this->table) as $chain => $policy)
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
						
			$iptablesRestore = array("*" . $this->table);
			
			foreach ($chains as $chain => $policy)
				$iptablesRestore[] = ":$chain $policy";
				
			$iptablesRestore = array_merge($iptablesRestore, $rules);	
			$iptablesRestore[] = "COMMIT";
						
			Console::execute("echo \"" . implode("\n", $iptablesRestore) . "\" | sudo /sbin/iptables-restore");
		}
		
		public function toHtml($title = "")
		{
			$out = <<<END
			
			<table id="firewall-table">
				<tr class="nodrag nodrop">
					<th colspan="5">$title</th>
				</tr>
				<tr class="nodrag nodrop">
					<th class="firewall-table-protocol-col">Proto</th>
					<th class="firewall-table-address-col">Source</th>
					<th class="firewall-table-port-col">Port</th>
					<th class="firewall-table-address-col">Destination</th>
					<th class="firewall-table-port-col">Port</th>
				</tr>
END;

			$divs = "";
						
			foreach ($this->rules as $rule)
			{
				$html = $rule->toHtml();
				
				$out .= $html->row;
				$divs .= $html->div . "\n";
			}
			
			return $out .= <<<END
			
			</table>
			$divs
END;
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
