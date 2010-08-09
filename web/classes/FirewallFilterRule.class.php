<?php
	require_once "DataHash.class.php";

	class FirewallFilterRule extends DataHash
	{
		public function __construct()
		{
			parent::__construct("firewall_filter_rules");
		}
		
		public function toJson($isDetail = false)
		{
			return json_encode($this->hashMap);
		}
		
		public function out()
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
	}
?>
