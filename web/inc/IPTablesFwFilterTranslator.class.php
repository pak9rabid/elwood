<?php
	require_once "Database.class.php";
	require_once "FirewallFilterSettings.class.php";
	require_once "FirewallChain.class.php";
	require_once "FirewallFilterRule.class.php";
	require_once "FwFilterTranslator.class.php";
	require_once "DbQueryPreper.class.php";
	require_once "NetUtils.class.php";
	
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
			TempDatabase::executeQuery(new DbQueryPreper("DELETE FROM firewall_chains WHERE table_name = 'filter'"));
			TempDatabase::executeQuery(new DbQueryPreper("DELETE FROM firewall_filter_rules"));
			
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
						$newChain->executeInsert(true);
						
						// Add entry into the counters array
						break;
					case "-":
						// Rule
						$newRule = new FirewallFilterRule();
						$newRule->setAttribute("chain_name", $lineElements[1]);
						
						for ($i = 2 ; $i<count($lineElements) ; $i++)
						{
							switch ($lineElements[$i])
							{
								case "-s":
									// Source address
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("src_addr", "!" . NetUtils::net2CIDR($lineElements[$i + 2]));
									else
										$newRule->setAttribute("src_addr", NetUtils::net2CIDR($lineElements[$i + 1]));
									break;
								case "-d":
									// Destination address
									if ($lineElements[$i + 1] == "!")
										$newRule->setAttribute("dst_addr", "!" . NetUtils::net2CIDR($lineElements[$i + 2]));
									else
										$newRule->setAttribute("dst_addr", NetUtils::net2CIDR($lineElements[$i + 1]));
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
										$newRule->setAttribute("icmp_type", "!" . NetUtils::icmpCode2Text($lineElements[$i + 1]));
									else
										$newRule->setAttribute("icmp_type", NetUtils::icmpCode2Text($lineElements[$i + 1]));
									break;
								case "-j":
									// Target
									$newRule->setAttribute("target", $lineElements[$i + 1]);
									break;
							}
						}
						
						$newRule->executeInsert(true);
						break;
				}
			}
		}
		
		public function setSystemFromDb($writeChanges)
		{
			// Syncs the system filter firewall to what's specified in the
			// database by generating and executing an iptables-restore script
			$rules = array();
			$iptablesRestore = array("*filter");
			
			foreach (FirewallFilterSettings::getChains() as $chain)
			{
				$chainName = $chain->getAttribute("chain_name");
				$rules[$chainName] = FirewallFilterSettings::getRules($chainName);
				$iptablesRestore[] = ":$chainName " . $chain->getAttribute("policy");
			}
			
			foreach ($rules as $chainName => $chainRules)
			{
				foreach ($chainRules as $rule)
				{
					$iptablesRule = "-A $chainName ";
					
					if ($rule->getAttribute("src_addr") != null)
					{
						// Source address
						$srcAddr = $rule->getAttribute("src_addr");
						$iptablesRule .= "-s ";
						
						if (self::beginsWith($srcAddr, "!"))
							$iptablesRule .= "! ";
							
						$iptablesRule .= trim($srcAddr, "!") . " ";
					}
					
					if ($rule->getAttribute("dst_addr") != null)
					{
						// Destination address
						$dstAddr = $rule->getAttribute("dst_addr");
						$iptablesRule .= "-d ";
						
						if (self::beginsWith($dstAddr, "!"))
							$iptablesRule .= "! ";
							
						$iptablesRule .= trim($dstAddr, "!") . " ";
					}
					
					if ($rule->getAttribute("state") != null)
					{
						// State
						$iptablesRule .= "-m state --state " . $rule->getAttribute("state") . " ";
					}
					
					if ($rule->getAttribute("fragmented") != null)
					{
						// Fragmented
						if ($rule->getAttribute("fragmented") == "Y")
							$iptablesRule .= "-f ";
						else
							$iptablesRule .= "! -f ";
					}
					
					if ($rule->getAttribute("in_interface") != null)
					{
						// In interface
						$inIface = $rule->getAttribute("in_interface");
						$iptablesRule .= "-i ";
						
						if (self::beginsWith($inIface, "!"))
							$iptablesRule .= "! ";
							
						$iptablesRule .= trim($inIface, "!") . " ";
					}
					
					if ($rule->getAttribute("out_interface") != null)
					{
						// Out interface
						$outIface = $rule->getAttribute("out_interface");
						$iptablesRule .= "-o ";
						
						if (self::beginsWith($outIface, "!"))
							$iptablesRule .= "! ";
							
						$iptablesRule .= trim($outIface, "!") . " ";
					}
					
					if ($rule->getAttribute("protocol") != null)
					{
						// Protocol
						$protocol = $rule->getAttribute("protocol");
						$iptablesRule .= "-p ";
						
						if (self::beginsWith($protocol, "!"))
							$iptablesRule .= "! ";
							
						$iptablesRule .= trim($protocol, "!") . " ";
					}
					
					// Implement protocol-specific (aka '-m' option)
					// rule options AFTER this comment
					if ($rule->getAttribute("sport") != null)
					{
						// Soure port
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "tcp" || $protocol == "udp")
						{
							$iptablesRule .= "-m $protocol ";
							$sport = $rule->getAttribute("sport");
						
							if (self::beginsWith($sport, "!"))
								$iptablesRule .= "! ";
								
							$iptablesRule .= "--sport " . trim($sport, "!") . " ";
						}
					}
					
					if ($rule->getAttribute("dport") != null)
					{
						// Destination port
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "tcp" || $protocol == "udp")
						{
							if (empty($sport))
								$iptablesRule .= "-m $protocol ";
								
							$dport = $rule->getAttribute("dport");
						
							if (self::beginsWith($dport, "!"))
								$iptablesRule .= "! ";
								
							$iptablesRule .= "--dport " . trim($dport, "!") . " ";
						}
					}
										
					if ($rule->getAttribute("icmp_type") != null)
					{
						// ICMP packet type
						$protocol = $rule->getAttribute("protocol");
						
						if ($protocol == "icmp")
						{
							$iptablesRule .= "-m icmp ";
							$icmpType = $rule->getAttribute("icmp_type");
							
							if (self::beginsWith($icmpType, "!"))
								$iptablesRule .= "! ";
								
							$iptablesRule .= "--icmp-type " . trim($icmpType, "!") . " ";
						}
					}
					
					if ($rule->getAttribute("target") != null)
					{
						// Rule target
						$iptablesRule .= "-j " . $rule->getAttribute("target") . " ";
					}
					
					$iptablesRestore[] = $iptablesRule;
				}
			}
			
			$iptablesRestore[] = "COMMIT";
			
			// If specified, write changes to the active firewall
			if ($writeChanges)
			{
				exec("echo \"" . implode("\n", $iptablesRestore) . "\" | sudo /sbin/iptables-restore", $placeholder, $returnVal);
				
				if ($returnVal != 0)
					throw new Exception("There was an error running iptables-restore");
			}
			
			return $iptablesRestore;
		}
		
		private static function beginsWith($str, $sub)
		{
			return (strncmp($str, $sub, strlen($sub)) == 0);
		}
	}
?>