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
	function saveRules()
	{
		var table = document.getElementById("firewall-table");
		var rules = new Array();
		    	
		for (i=0 ; i<table.rows.length ; i++)
		{
			if (table.rows[i].id)
			rules.push(table.rows[i].id);
		}

		var stateChangeFunc = function()
		{
			if (xhr.readyState != 4)
				return;

			if (xhr.status != 200)
				return;

			var response;

			if (JSON.parse)
				// Use the secure method of parsing JSON response, if available
				response = JSON.parse(xhr.responseText);
			else
				// Less secure, but compatible
				response = eval("(" + xhr.responseText + ")");

			if (response.result)
			{
				document.getElementById("fwActions").innerHTML = "<span style=\"color: green;\">Changes saved successfully</span>";
				fade(document.getElementById("fwActions"));
			}
			else
			{
				document.getElementById("fwActions").innerHTML = "<span style=\"color: red;\">Unable to save changes</span>";
				fade(document.getElementById("fwActions"));
			}
		};

		sendAjaxRequest("ajax/editFwFilterRules.php?dir=<?=$direction?>&order=" + rules, stateChangeFunc, "GET");
	}
	</script>
</head>

<body onLoad="dndInit()">
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
			<input type="button" value="Add Rule" onClick="addEditFilterRuleDlg()" />
			<div id="fwTable">
				<?=$fwFilter->out($direction)?>
			</div>
			<div id="fwActions">&nbsp;</div>
		</div>
	</div>
	<div id="hideshow" style="visibility: hidden;">
		<div id="fade"></div>
		<div class="popup_block">
			<div id="fwAddEditFilterRuleMsgs"></div>
			<form name="addEditRuleForm" action="javascript:submitAddEditRule()">
				<input type="hidden" name="ruleId" />
				<input type="hidden" name="dir" value="<?=$direction?>" />
				<table>
					<tr>
						<td class="tabInputLabel">Protocol:</td>
						<td class="tabInputValue">
							<select name="protocol">
								<option value="any">any</option>
							<?php
								foreach (NetUtils::getNetworkProtocols() as $protocol)
									echo "<option value=\"$protocol\">$protocol</option>\n";
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">ICMP Type:</td>
						<td class="tabInputValue">
							<select name="icmpType">
							<?php 
								foreach (array_values(NetUtils::getIcmpTypes()) as $icmpType)
									echo "<option value=\"$icmpType\">$icmpType</option>\n";
							?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Source Address:</td>
						<td class="tabInputValue"><input type="text" name="srcAddr" size="20" maxlength="20" /></td>
					</tr>
					<tr>
						<td class="tabInputLabel">Source Port:</td>
						<td class="tabInputValue"><input type="text" name="srcPort" size="20" maxlength="5" /></td>
					</tr>
					<tr>
						<td class="tabInputLabel">Destination Address:</td>
						<td class="tabInputValue"><input type="text" name="dstAddr" size="20" maxlength="20" /></td>
					</tr>
					<tr>
						<td class="tabInputLabel">Destination Port:</td>
						<td class="tabInputValue"><input type="text" name="dstPort" size="20" maxlength="5" /></td>
					</tr>
					<tr>
						<td class="tabInputLabel">Connection State:</td>
						<td class="tabInputValue">
							<?php 
								foreach (NetUtils::getConnectionStates() as $connState)
									echo "<input type=\"checkbox\" name=\"connState\" value=\"$connState\" />$connState&nbsp;\n";
							?>
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Fragmented:</td>
						<td class="tabInputValue">
							<select name="fragmented">
								<option value="any">any</option>
								<option value="Y">Yes</option>
								<option value="N">No</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="tabInputLabel">Target:</td>
						<td class="tabInputValue">
							<select name="target">
								<option value="DROP">DROP</option>
								<option value="ACCEPT">ACCEPT</option>
							</select>
						</td>
					</tr>
					<tr>
						<td colspan="2">&nbsp;</td>
					</tr>
					<tr>
						<td colspan="2" align="center">
							<input type="submit" value="Save" />&nbsp;
							<input id="saveAsNewBtn" disabled type="submit" value="Save As New" onClick="document.addEditRuleForm.ruleId.value = null; return true;" />&nbsp;
							<input id="deleteBtn" disabled type="submit" value="Delete" onClick="deleteRule(document.addEditRuleForm.ruleId.value); return false;" />&nbsp;
							<input type="button" value="Cancel" onClick="closeAddEditRule()" /></td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</body>
</html>