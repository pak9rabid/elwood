<?php 
	require_once "accessControl.php";
	require_once "RouterSettings.class.php";
	require_once "ClassFactory.class.php";
	require_once "FirewallFilterTable.class.php";
	require_once "routertools.inc";
	require_once "formatting.inc";
	
	$extIf = RouterSettings::getSettingValue("EXTIF");
	$intIf = RouterSettings::getSettingValue("INTIF");
	$fwTranslator = ClassFactory::getFwFilterTranslator();
	$fwTranslator->setDbFromSystem();
	$fwFilter = new FirewallFilterTable();
	$policy = $fwFilter->getChain("FORWARD")->getAttribute("policy");
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
			<table border="0" align="center">
				<tr>
					<td valign="top">
						<?php printFwTable("Incoming", $policy, $fwFilter->getRules("forward_in")); ?>
					</td>
					<td valign="top">
						<?php printFwTable("Outgoing", $policy, $fwFilter->getRules("forward_out")); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
</body>
</html>

<?php 
	function printFwTable($direction, $policy, array $rules)
	{
		$strongRed = "#FF0000";
		$strongGreen = "#00FF00";
		$red = "#FFAAAA";
		$green = "#99FF99";
		$tableBgColor = $policy == "ACCEPT" ? $strongGreen : $strongRed;
		$url = "scripts/change_policy.php?policy=" . $policy == "ACCEPT" ? "DROP" : "ACCEPT";
		$winProps = "height=90, width=250";
		
		echo "<table id=\"firewall-table\">\n";
		echo "<tr><th colspan=\"2\" bgcolor=\"$tableBgColor\">$direction Traffic";
		
		if ($group == "admins")
			echo "<br /><a href=\"$url\" onClick=\"popUp('$url', '$winProps')\" style=\"font-size: 8pt;\">[Change Policy]</a>";
			
		echo "</th></tr>\n";
		echo "<tr><th bgcolor=\"$tableBgColor\">Port</th><th bgcolor=\"$tableBgColor\">Protocol</th></tr>\n";
		
		if (!empty($rules))
		{
			foreach ($rules as $rule)
			{
				$rowBgColor = $rule->getAttribute("target") == ACCEPT ? $green : $red;
				$port = $rule->getAttribute("dport") == null ? "N/A" : $rule->getAttribute("dport");
				$protocol = $rule->getAttribute("protocol") == null ? "N/A" : $rule->getAttribute("protocol");
				
				echo "<tr><td bgcolor=\"$rowBgColor\">$port</td><td bgcolor=\"$rowBgColor\">$protocol</td></tr>\n";
			}
		}
		else
			echo "<tr><td colspan=\"2\">None</td></tr>\n";
		
		echo "</table>\n";
	}
?>