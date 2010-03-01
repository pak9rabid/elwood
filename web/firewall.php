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
	//$policy = $fwFilter->getChain("FORWARD")->getAttribute("policy");
	$direction = $_REQUEST['dir'] == null ? "in" : $_REQUEST['dir'];
?>

<html>
<head>
	<title>Firewall Setup</title>
	<link rel="StyleSheet" type="text/css", href="routerstyle.css" />
	<script language="JavaScript" src="inc/firewall.js" type="text/javascript"></script>
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
		$strongRed = "#FF0000";
		$strongGreen = "#00FF00";
		$red = "#FFAAAA";
		$green = "#99FF99";
		$tableBgColor = $policy == "ACCEPT" ? $strongGreen : $strongRed;
		
		echo "<table id=\"firewall-table\">\n" .
			 "	<tr bgcolor=\"$tableBgColor\"><th colspan=\"5\">" . ($direction == "in" ? "Incoming" : "Outgoing") . "Traffic</th></tr>\n" .
			 "	<tr bgcolor=\"$tableBgColor\"><th>Proto</th><th>Source</th><th>Port</th><th>Destination</th><th>Port</th></tr>\n";
		
		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				$rowBgColor = $rule->getAttribute("target") == "ACCEPT" ? $green : $red;
				$proto = getRuleAttr($rule, "protocol");
				$srcAddr = getRuleAttr($rule, "src_addr") != "*" ? NetUtils::net2CIDR(getRuleAttr($rule, "src_addr")) : "*";
				$srcPort = getRuleAttr($rule, "sport");
				$dstAddr = getRuleAttr($rule, "dst_addr") != "*" ? NetUtils::net2CIDR(getRuleAttr($rule, "dst_addr")) : "*";
				$dstPort = getRuleAttr($rule, "dport");
				
				echo "	<tr bgcolor=\"$rowBgColor\"><td>$proto</td><td>$srcAddr</td><td>$srcPort</td><td>$dstAddr</td><td>$dstPort</td></tr>\n";
			}
		}
		else
			echo "	<tr><td colspan=\"5\">None</td><tr>\n";
		
		echo "</table>\n";
	}
	
	function getRuleAttr(FirewallFilterRule $rule, $attribute)
	{
		return $rule->getAttribute($attribute) == null ? "*" : $rule->getAttribute($attribute);
	}
?>