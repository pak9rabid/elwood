<?php
	require_once "Database.class.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "FwFilterTranslator.class.php";
	
	class IPTablesFwFilterTranslator implements FwFilterTranslator
	{
		public function setDbFromSystem()
		{
			// Reads the current system firewall settings by running and
			// parsing the results of iptables-save on the 'filter' table
			$shellCmd = "sudo /sbin/iptables-save -t filter";
			$output = array();
			
			exec($shellCmd, $output);
			
			// Clear chains and rules related to filtering
			Database::executeQuery("DELETE FROM firewall_chains WHERE table_name = 'filter'");
			Database::executeQuery("DELETE FROM firewall_filter_rules");
			
			$ruleCounters = array();
			
			foreach ($output as $line)
			{
				$lineElements = explode(" ", $line);
				
				switch (substr($line, 0, 1))
				{
					case ":":
						// Chain
						$newChain = new FirewallChain();
						$newChain->setAttribute("table_name", "filter");
						$newChain->setAttribute("chain_name", trim($lineElements[0], ":"));
						$newChain->setAttribute("policy", $lineElements[1]);
						$newChain->executeInsert();
						
						// Add entry into the counters array
						$ruleCounters[$newChain->getAttribute("chain_name")] = 0;
						break;
					case "-":
						// Rule
						$newRule = new FirewallFilterRule();
						$newRule->setAttribute("id", FirewallFilterRule::generateUniqueID());
						$newRule->setAttribute("chain_name", $lineElements[1]);
						$newRule->setAttribute("rule_number", $ruleCounters[$lineElements[1]]++);
						
						for ($i = 2 ; $i<count($lineElements) ; $i++)
						{
							switch ($lineElements[$i])
							{
								case "-s":
									// Source address
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("src_addr", "!" . $lineElements[$i + 2]);
									else
										$newRule->setAttribute("src_addr", $lineElements[$i + 1]);
									break;
								case "-d":
									// Destination address
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("dst_addr", "!" . $lineElements[$i + 2]);
									else
										$newRule->setAttribute("dst_addr", $lineElements[$i + 1]);
									break;
								case "--state":
									// State
									$newRule->setAttribute("state", $lineElements[$i + 1]);
									break;
								case "-f":
									// Fragmented
									if ($lineElements[$i - 1] == "!")
										$newRule->setAttribute("fragmented", "N");
									else
										$newRule->setAttribute("fragmented", "Y");
									break;
								case "-i":
									// Input interface
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("in_interface", "!" . $lineElements[$i + 2]);
									else
										$newRule->setAttribute("in_interface", $lineElements[$i + 1]);
									break;
								case "-o":
									// Output interface
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("out_interface", "!" . $lineElements[$i + 2]);
									else
										$newRule->setAttribute("out_interface", $lineElements[$i + 1]);
									break;
								case "-p":
									// Protocol
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("protocol", "!" . $lineElements[$i + 2]);
									else
										$newRule->setAttribute("protocol", $lineElements[$i + 1]);
									break;
								case "--dport":
									// Destination port
									if ($lineElements[$i - 1] == "!")
										$newRule->setAttribute("dport", "!" . $lineElements[$i + 1]);
									else
										$newRule->setAttribute("dport", $lineElements[$i + 1]);
									break;
								case "--sport":
									// Source port
									if ($lineElements[$i - 1] == "!")
										$newRule->setAttribute("sport", "!" . $lineElements[$i + 1]);
									else
										$newRule->setAttribute("sport", $lineElements[$i + 1]);
									break;
								case "--icmp-type":
									// ICMP packet type
									if ($lineElements[$i - 1] == "!")
										$newRule->setAttribute("icmp_type", "!" . $lineElements[$i + 1]);
									else
										$newRule->setAttribute("icmp_type", $lineElements[$i + 1]);
									break;
								case "-j":
									// Target
									$newRule->setAttribute("target", $lineElements[$i + 1]);
									break;
							}
						}
						
						$newRule->executeInsert();
						break;
				}
			}
		}
		
		public function setSystemFromDb()
		{
			// Syncs the system filter firewall to what's specified in the
			// database by generating and executing an iptables-restore script
			$rules = array();
			$iptablesRestore = "*filter\n";
			
			foreach (FirewallFilterSettings::getChains() as $chain)
			{
				$chainName = $chain->getAttribute("chain_name");
				$rules[$chainName] = FirewallFilterSettings::getRules($chainName);
				$iptablesRestore .= ":$chainName " . $chain->getAttribute("policy") . "\n";
			}
			
			foreach ($rules as $chainName => $chainRules)
			{
				foreach ($chainRules as $rule)
				{
					$iptablesRestore .= "-A $chainName ";
					
					if ($rule->getAttribute("src_addr") != null)
					{
						// Source address
						$srcAddr = $rule->getAttribute("src_addr");
						$iptablesRestore .= "-s ";
						
						if (self::beginsWith($srcAddr, "!"))
							$iptablesRestore .= "! ";
							
						$iptablesRestore .= trim($srcAddr, "!") . " ";
					}
					
					if ($rule->getAttribute("dst_addr") != null)
					{
						// Destination address
						$dstAddr = $rule->getAttribute("dst_addr");
						$iptablesRestore .= "-d ";
						
						if (self::beginsWith($dstAddr, "!"))
							$iptablesRestore .= "! ";
							
						$iptablesRestore .= trim($dstAddr, "!") . " ";
					}
					
					if ($rule->getAttribute("state") != null)
					{
						// State
						$iptablesRestore .= "-m state --state " . $rule->getAttribute("state") . " ";
					}
					
					if ($rule->getAttribute("fragmented") != null)
					{
						// Fragmented
						if ($rule->getAttribute("fragmented") == "Y")
							$iptablesRestore .= "-f ";
						else
							$iptablesRestore .= "! -f ";
					}
					
					if ($rule->getAttribute("in_interface") != null)
					{
						// In interface
						$inIface = $rule->getAttribute("in_interface");
						$iptablesRestore .= "-i ";
						
						if (self::beginsWith($inIface, "!"))
							$iptablesRestore .= "! ";
							
						$iptablesRestore .= trim($inIface, "!") . " ";
					}
					
					if ($rule->getAttribute("out_interface") != null)
					{
						// Out interface
						$outIface = $rule->getAttribute("out_interface");
						$iptablesRestore .= "-o ";
						
						if (self::beginsWith($outIface, "!"))
							$iptablesRestore .= "! ";
							
						$iptablesRestore .= trim($outIface, "!") . " ";
					}
					
					if ($rule->getAttribute("protocol") != null)
					{
						// Protocol
						$protocol = $rule->getAttribute("protocol");
						$iptablesRestore .= "-p ";
						
						if (self::beginsWith($protocol, "!"))
							$iptablesRestore .= "! ";
							
						$iptablesRestore .= trim($protocol, "!") . " ";
					}
					
					// Implement protocol-specific (aka '-m' option)
					// rule options AFTER this comment
					if ($rule->getAttribute("sport") != null)
					{
						// Soure port
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "tcp" || $protocol == "udp")
						{
							$iptablesRestore .= "-m $protocol ";
							$sport = $rule->getAttribute("sport");
						
							if (self::beginsWith($sport, "!"))
								$iptablesRestore .= "! ";
								
							$iptablesRestore .= "--sport " . trim($sport, "!") . " ";
						}
					}
					
					if ($rule->getAttribute("dport") != null)
					{
						// Destination port
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "tcp" || $protocol == "udp")
						{
							if (empty($sport))
								$iptablesRestore .= "-m $protocol ";
								
							$dport = $rule->getAttribute("dport");
						
							if (self::beginsWith($dport, "!"))
								$iptablesRestore .= "! ";
								
							$iptablesRestore .= "--dport " . trim($dport, "!") . " ";
						}
					}
										
					if ($rule->getAttribute("icmp_type") != null)
					{
						// ICMP packet type
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "icmp")
						{
							$iptablesRestore .= "-m icmp ";
							$icmpType = $rule->getAttribute("icmp_type");
							
							if (self::beginsWith($icmpType, "!"))
								$iptablesRestore .= "! ";
								
							$iptablesRestore .= "--icmp-type " . trim($icmpType, "!") . " ";
						}
					}
					
					if ($rule->getAttribute("target") != null)
					{
						// Rule target
						$iptablesRestore .= "-j " . $rule->getAttribute("target") . " ";
					}
					
					$iptablesRestore .= "\n";
				}
			}
			
			$iptablesRestore .= "COMMIT\n";
			
			// Testing
			echo "\n$iptablesRestore\n\n";
			// End Testing
		}
		
		private static function beginsWith($str, $sub)
		{
			return (strncmp($str, $sub, strlen($sub)) == 0);
		}
	}
?>