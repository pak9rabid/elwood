<?php
	require_once "DataHash.class.php";
	require_once "StringUtils.class.php";
	require_once "NetUtils.class.php";
	
	class FirewallRule extends DataHash
	{
		public function __construct()
		{
			parent::__construct("firewall_rules");
		}
				
		public function toIptablesRule()
		{
			$iptablesRule = "-A " . $this->getAttribute("chain_name") . " ";
			
			if ($this->getAttribute("int_in") != null)
			{
				// Input interface
				$intIn = $this->getAttribute("int_in");
				$iptablesRule .= "-i ";
				
				if (StringUtils::beginsWith($intIn, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($intIn, "!") . " ";
			}
					
			if ($this->getAttribute("int_out") != null)
			{
				// Output interface
				$intOut = $this->getAttribute("int_out");
				$iptablesRule .= "-o ";
						
				if (StringUtils::beginsWith($intOut, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($intOut, "!") . " ";
			}
					
			if ($this->getAttribute("src_addr") != null)
			{
				// Source address
				$srcAddr = $this->getAttribute("src_addr");
				$iptablesRule .= "-s ";
					
				if (StringUtils::beginsWith($srcAddr, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($srcAddr, "!") . " ";
			}
					
			if ($this->getAttribute("dst_addr") != null)
			{
				// Destination address
				$dstAddr = $this->getAttribute("dst_addr");
				$iptablesRule .= "-d ";
					
				if (StringUtils::beginsWith($dstAddr, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($dstAddr, "!") . " ";
			}
					
			if ($this->getAttribute("state") != null)
			{
				// State
				$iptablesRule .= "-m state --state " . $this->getAttribute("state") . " ";
			}
					
			if ($this->getAttribute("fragmented") != null)
			{
				// Fragmented
				if ($this->getAttribute("fragmented") == "Y")
					$iptablesRule .= "-f ";
				else
					$iptablesRule .= "! -f ";
			}
					
			if ($this->getAttribute("in_interface") != null)
			{
				// In interface
				$inIface = $this->getAttribute("in_interface");
				$iptablesRule .= "-i ";
						
				if (StringUtils::beginsWith($inIface, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($inIface, "!") . " ";
			}
					
			if ($this->getAttribute("out_interface") != null)
			{
				// Out interface
				$outIface = $this->getAttribute("out_interface");
				$iptablesRule .= "-o ";
						
				if (StringUtils::beginsWith($outIface, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($outIface, "!") . " ";
			}
					
			if ($this->getAttribute("protocol") != null)
			{
				// Protocol
				$protocol = $this->getAttribute("protocol");
				$iptablesRule .= "-p ";
						
				if (StringUtils::beginsWith($protocol, "!"))
					$iptablesRule .= "! ";
							
				$iptablesRule .= trim($protocol, "!") . " ";
			}
					
			// Implement protocol-specific (aka '-m' option)
			// rule options AFTER this comment
			if ($this->getAttribute("sport") != null)
			{
				// Soure port
				$protocol = $this->getAttribute("protocol");
						
				if ($protocol == "tcp" || $protocol == "udp")
				{
					$iptablesRule .= "-m $protocol ";
					$sport = $this->getAttribute("sport");
						
					if (StringUtils::beginsWith($sport, "!"))
						$iptablesRule .= "! ";
								
					$iptablesRule .= "--sport " . trim($sport, "!") . " ";
				}
			}
					
			if ($this->getAttribute("dport") != null)
			{
				// Destination port
				$protocol = $this->getAttribute("protocol");
				
				if ($protocol == "tcp" || $protocol == "udp")
				{
					if (empty($sport))
						$iptablesRule .= "-m $protocol ";
						
					$dport = $this->getAttribute("dport");
					
					if (StringUtils::beginsWith($dport, "!"))
						$iptablesRule .= "! ";
								
					$iptablesRule .= "--dport " . trim($dport, "!") . " ";
				}
			}
										
			if ($this->getAttribute("icmp_type") != null)
			{
				// ICMP packet type
				$protocol = $this->getAttribute("protocol");
				
				if ($protocol == "icmp")
				{
					$iptablesRule .= "-m icmp ";
					$icmpType = $this->getAttribute("icmp_type");
							
					if (StringUtils::beginsWith($icmpType, "!"))
						$iptablesRule .= "! ";
								
					$iptablesRule .= "--icmp-type " . trim($icmpType, "!") . " ";
				}
			}
					
			if ($this->getAttribute("target") != null)
			{
				// Rule target
				$iptablesRule .= "-j " . $this->getAttribute("target") . " ";
			}
					
			return $iptablesRule;
		}
		
		public function fromIptablesRule($rule)
		{
			$ruleElements = explode(" ", $rule);
			
			if ($ruleElements[0] != "-A")
				throw new Exception("Invalid IPTables rule specified");
				
			$this->setAttribute("chain_name", $ruleElements[1]);
			
			for ($i=2 ; $i<count($ruleElements) ; $i++)
			{
				switch ($ruleElements[$i])
				{
					case "-i":
						// Input interface
						if ($ruleElements[$i + 1] == "!")
							$this->setAttribute("int_in", "!" . $ruleElements[$i + 2]);
						else
							$this->setAttribute("int_in", $ruleElements[$i + 1]);
						break;
					case "-o":
						// Output interface
						if ($ruleElements[$i + 1] == "!")
							$this->setAttribute("int_out", "!" . $ruleElements[$i + 2]);
						else
							$this->setAttribute("int_out", $ruleElements[$i + 1]);
						break;
					case "-s":
						// Source address
						if ($ruleElements[$i + 1] == "!")
							$this->setAttribute("src_addr", "!" . NetUtils::net2CIDR($ruleElements[$i + 2]));
						else
							$this->setAttribute("src_addr", NetUtils::net2CIDR($ruleElements[$i + 1]));
						break;
					case "-d":
						// Destination address
						if ($ruleElements[$i + 1] == "!")
							$this->setAttribute("dst_addr", "!" . NetUtils::net2CIDR($ruleElements[$i + 2]));
						else
							$this->setAttribute("dst_addr", NetUtils::net2CIDR($ruleElements[$i + 1]));
						break;
					case "--state":
						// State
						$this->setAttribute("state", $ruleElements[$i + 1]);
						break;
					case "-f":
						// Fragmented
						if ($ruleElements[$i - 1] == "!")
							$this->setAttribute("fragmented", "N");
						else
							$this->setAttribute("fragmented", "Y");
						break;
					case "-p":
						// Protocol
						if ($ruleElements[$i + 1] == "!")
							$this->setAttribute("protocol", "!" . $ruleElements[$i + 2]);
						else
							$this->setAttribute("protocol", $ruleElements[$i + 1]);
						break;
					case "--dport":
						// Destination port
						if ($ruleElements[$i - 1] == "!")
							$this->setAttribute("dport", "!" . $ruleElements[$i + 1]);
						else
							$this->setAttribute("dport", $ruleElements[$i + 1]);
						break;
					case "--sport":
						// Source port
						if ($ruleElements[$i - 1] == "!")
							$this->setAttribute("sport", "!" . $ruleElements[$i + 1]);
						else
							$this->setAttribute("sport", $ruleElements[$i + 1]);
						break;
					case "--icmp-type":
						// ICMP packet type
						if ($ruleElements[$i - 1] == "!")
							$this->setAttribute("icmp_type", "!" . NetUtils::icmpCode2Text($ruleElements[$i + 1]));
						else
							$this->setAttribute("icmp_type", NetUtils::icmpCode2Text($ruleElements[$i + 1]));
						break;
					case "-j":
						// Target
						$this->setAttribute("target", $ruleElements[$i + 1]);
						break;
				}
			}
		}
	
		public function toHtml()
		{		
			$rowClass = $this->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop";
			$proto = $this->getAttributeDisp("protocol");
			$srcAddr = $this->getAttributeDisp("src_addr");
			$srcPort = $this->getAttributeDisp("sport");
			$dstAddr = $this->getAttributeDisp("dst_addr");
			$dstPort = $this->getAttributeDisp("dport");
			$ruleId = $this->getAttribute("id") != null && $this->getAttribute("id") != "" ? $this->getAttribute("id") : "new" . uniqid();

			$row =	"<tr id=\"$ruleId\" class=\"$rowClass\">" .
					"<td>$proto</td><td>$srcAddr</td><td>$srcPort</td>" .
					"<td>$dstAddr</td><td>$dstPort</td>" .
					"</tr>\n";
			
			$div =	"<div id=\"" . $ruleId . "details\" class=\"fwRuleDetails\">\n" .
					"	<table class=\"fwDetailsTable\">\n" .
					"		<tr><td class=\"label\">Protocol:</td><td id=\"" . $ruleId . "protocol\">$proto</td></tr>\n" .
					"		<tr><td class=\"label\">Source Address:</td><td id=\"" . $ruleId . "src_addr\">$srcAddr</td></tr>\n" .
					"		<tr><td class=\"label\">Source Port:</td><td id=\"" . $ruleId . "sport\">$srcPort</td></tr>\n" .
					"		<tr><td class=\"label\">Destination Address:</td><td id=\"" . $ruleId . "dst_addr\">$dstAddr</td></tr>\n" .
					"		<tr><td class=\"label\">Destination Port:</td><td id=\"" . $ruleId . "dport\">$dstPort</td></tr>\n" .
					"		<tr><td class=\"label\">States:</td><td id=\"" . $ruleId . "state\">" . $this->getAttributeDisp("state") . "</td></tr>\n" .
					"		<tr><td class=\"label\">Fragmented:</td><td id=\"" . $ruleId . "fragmented\">" . $this->getAttributeDisp("fragmented") . "</td></tr>\n";
				
			if ($proto == "icmp")
				$div .= "	<tr><td class=\"label\">ICMP Type:</td><td id=\"" . $ruleId . "icmp_type\">" . $this->getAttributeDisp("icmp_type") . "</td></tr>\n";
					
			$div .= "		<tr><td class=\"label\">Target:</td><td id=\"" . $ruleId . "target\">" . $this->getAttributeDisp("target") . "</td></tr>\n" .
					"	</table>\n" .
					"</div>\n";
			
			return (object) array	(
										"row" => $row,
										"div" => $div
									);
		}
		
		public function validate()
		{
			// returns a list of any errors that exist,
			// or an empty array if no errors exist
			$errors = array();
			
			foreach ($this->hashMap as $key => $value)
			{
				switch ($key)
				{
					case "src_addr":
						if (!NetUtils::isValidIp($value) && !NetUtils::isValidNetwork($value))
							$errors[] = "Invalid source address specified";
						break;
					case "dst_addr":
						if (!NetUtils::isValidIp($value) && !NetUtils::isValidNetwork($value))
							$errors[] = "Invalid destination address specified";
						break;
					case "fragmented":
						if ($value != "Y" && $value != "N")
							$errors[] = "Invalid fragmented value specified";
						break;
					case "protocol":
						if (!NetUtils::isValidProtocol($value))
							$errors[] = "Invalid protocol specified";
						break;
					case "dport":
						$protocol = $this->getAttribute("protocol");
						
						if (empty($protocol) || !in_array($protocol, array("tcp", "udp")))
							$errors[] = "Protocol must be either tcp or udp if a destination port is to be specified";
							
						if (!NetUtils::isValidIanaPortNumber(($value)))
							$errors[] = "Invalid destination port specified";
						break;
					case "sport":
						$protocol = $this->getAttribute("protocol");
						
						if (empty($protocol) || !in_array($protocol, array("tcp", "udp")))
							$errors[] = "Protocol must be either tcp or udp if a source port is to be specified";
							
						if (!NetUtils::isValidIanaPortNumber(($value)))
							$errors[] = "Invalid source port specified";
						break;
					case "icmp_type":
						if (!NetUtils::isValidIcmpType($value))
							$errors[] = "Invalid ICMP type specified";
						break;
					case "state":
						if (!NetUtils::isValidIpState(explode(",", $value)))
							$errors[] = "Invalid IP state specified";
						break;
					case "table_name":
						if (!NetUtils::isValidIPTablesTable($value))
							$errors[] = "Invalid IPTables table specified";
						break;
				}
			}
			
			return $errors;
		}
	}
?>