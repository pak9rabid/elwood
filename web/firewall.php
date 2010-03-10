<?php 
	require_once "accessControl.php";
	
	require_once "RouterSettings.class.php";
	require_once "ClassFactory.class.php";
	require_once "FirewallFilterTable.class.php";
	require_once "NetUtils.class.php";
	
	require_once "routertools.inc";
	require_once "formatting.inc";
	
	$extIf = RouterSettings::getSettingValue("EXTIF");
	$intIf = RouterSettings::getSettingValue("INTIF");
	$fwTranslator = ClassFactory::getFwFilterTranslator();
	$fwTranslator->setDbFromSystem();
	$fwFilter = new FirewallFilterTable();
	$direction = $_REQUEST['dir'] == null ? "in" : $_REQUEST['dir'];
?>

<html>
<head>
	<title>Firewall Setup</title>
	<link rel="StyleSheet" type="text/css", href="routerstyle.css" />
	<script language="JavaScript" src="inc/ajax.js" type="text/javascript"></script>
	<script language="JavaScript" src="inc/firewall.js" type="text/javascript"></script>
	<script language="JavaScript" type="text/javascript">
		function showRule(row, ruleId)
		{
			var ruleDetails = document.getElementById(ruleId + "details");
			var pos = getElementPosition(row);
			var posLeft = pos[0];
			var posTop = pos[1] + 25;
			
			ruleDetails.style.position = "absolute";
			ruleDetails.style.left = posLeft + "px";
			ruleDetails.style.top = posTop + "px";
			ruleDetails.style.display = "inline";
		}

		function hideRule(ruleId)
		{
			document.getElementById(ruleId + "details").style.display = "none";
		}

		function getElementPosition(element)
		{
			var curleft = curtop = 0;

			if (element.offsetParent)
			{
				do
				{
					curleft += element.offsetLeft;
					curtop += element.offsetTop;
				} while (element = element.offsetParent);
			}

			return [curleft, curtop];
		}
	</script>
</head>

<body>
	<div id="container">
		<div id="title">
			<?php echo printTitle("Firewall"); ?>
		</div>
		<?php printNavigation(); ?>
		<div id="content">
			<a href="firewall.php?dir=in">Incoming</a>
			&nbsp;
			<a href="firewall.php?dir=out">Outgoing</a>
			<br /><br />
			<?php printFwTable($direction, $fwFilter); ?>
		</div>
	</div>
</body>
</html>

<?php
	function printFwTable($direction, FirewallFilterTable $filter)
	{
		$policy = $filter->getChain("FORWARD")->getAttribute("policy");
		$rules = $direction == "in" ? $filter->getRules("forward_in") : $filter->getRules("forward_out");		
		$policyClass = $policy == "ACCEPT" ? "fwPolicyAccept" : "fwPolicyDrop";
		$ruleDivs = "";
		
		echo "<table id=\"firewall-table\">\n" .
			 "	<tr class=\"$policyClass\"><th colspan=\"5\">" . ($direction == "in" ? "Incoming" : "Outgoing") . "Traffic</th></tr>\n" .
			 "	<tr class=\"$policyClass\"><th>Proto</th><th>Source</th><th>Port</th><th>Destination</th><th>Port</th></tr>\n";
		
		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				$rowClass = $rule->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop";
				$proto = getRuleAttr($rule, "protocol");
				$srcAddr = getRuleAttr($rule, "src_addr") != "*" ? NetUtils::net2CIDR(getRuleAttr($rule, "src_addr")) : "*";
				$srcPort = getRuleAttr($rule, "sport");
				$dstAddr = getRuleAttr($rule, "dst_addr") != "*" ? NetUtils::net2CIDR(getRuleAttr($rule, "dst_addr")) : "*";
				$dstPort = getRuleAttr($rule, "dport");
				$ruleId = getRuleAttr($rule, "id");
				
				echo "<tr id=\"rowid$ruleId\" class=\"$rowClass\" onMouseOver=\"showRule(this, $ruleId)\" " .
					 "onMouseOut=\"hideRule($ruleId)\"><td>$proto</td><td>$srcAddr</td><td>$srcPort</td>" .
					 "<td>$dstAddr</td><td>$dstPort</td></tr>\n";
				
				// Create div to store rule details
				$ruleDivs .= "<div id=\"" . $ruleId . "details\" class=\"fwRuleDetails\">\n" .
							 "	<table class=\"fwDetailsTable\">\n" .
							 "		<tr><td class=\"label\">Protocol:</td><td>$proto</td></tr>\n" .
							 "		<tr><td class=\"label\">Source Address:</td><td>$srcAddr</td></tr>\n" .
							 "		<tr><td class=\"label\">Source Port:</td><td>$srcPort</td></tr>\n" .
							 "		<tr><td class=\"label\">Destination Address:</td><td>$dstAddr</td></tr>\n" .
							 "		<tr><td class=\"label\">Destination Port:</td><td>$dstPort</td></tr>\n" .
							 "		<tr><td class=\"label\">States:</td><td>" . getRuleAttr($rule, "state") . "</td></tr>\n" .
							 "		<tr><td class=\"label\">Fragmented:</td><td>" . getRuleAttr($rule, "fragmented") . "</td></tr>\n";
				
				if ($proto == "icmp")
					$ruleDivs .= "		<tr><td class=\"label\">ICMP Type:</td><td>" . getRuleAttr($rule, "icmp_type") . "</td></tr>\n";
					
				$ruleDivs .= "		<tr><td class=\"label\">Target:</td><td>" . getRuleAttr($rule, "target") . "</td></tr>\n" .
							 "	</table>\n" .
							 "</div>\n";
			}
		}
		else
			echo "	<tr><td colspan=\"5\">None</td><tr>\n";
		
		echo "</table>\n";
		echo $ruleDivs;
		
	}
	
	function getRuleAttr(FirewallFilterRule $rule, $attribute)
	{
		return $rule->getAttribute($attribute) == null ? "*" : $rule->getAttribute($attribute);
	}
?>