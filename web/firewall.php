<?php 
	require_once "accessControl.php";
	
	require_once "TempDatabase.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallFilterTable.class.php";
	require_once "NetUtils.class.php";
	
	require_once "routertools.inc";
	require_once "formatting.inc";
	
	TempDatabase::create();
	
	$extIf = RouterSettings::getSettingValue("EXTIF");
	$intIf = RouterSettings::getSettingValue("INTIF");
	$fwFilter = new FirewallFilterTable();
	$direction = $_REQUEST['dir'] == null ? "in" : $_REQUEST['dir'];
?>

<html>
<head>
	<title>Firewall Setup</title>
	<link rel="StyleSheet" type="text/css", href="routerstyle.css" />
	<script src="inc/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="inc/jquery.tablednd_0_5.js" type="text/javascript"></script>
	<script src="inc/firewall.js" type="text/javascript"></script>
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
			<button id="addRuleBtn">Add Rule</button>
			<button id="changePolicyBtn">Change Policy</button>
			<div id="fwTable">
				<?=$fwFilter->out($direction)?>
			</div>
			<div id="fwActions">
				<input id="saveBtn" type="button" value="Save Rules" />
				<div id="fwResults"></div>
			</div>
		</div>
	</div>
	<div id="hideshow">
		<div id="fade"></div>
		<div class="popup_block">
			<div id="fwAddEditFilterRuleMsgs"></div>
			<form id="addEditRuleForm">
				<input type="hidden" id="ruleId" name="ruleId" value="" />
				<input type="hidden" id="dir" name="dir" value="<?=$direction?>" />
				<table>
					<tr>
						<!-- Protocol -->
						<td class="tabInputLabel">Protocol:</td>
						<td class="tabInputValue">
							<select id="protocol">
								<option value="any">any</option>
							<?php
								foreach (NetUtils::getNetworkProtocols() as $protocol)
									echo "<option value=\"$protocol\">$protocol</option>\n";
							?>
							</select>
						</td>
					</tr>
					<tr>
						<!-- ICMP Type -->
						<td class="tabInputLabel">ICMP Type:</td>
						<td class="tabInputValue">
							<select id="icmpType">
							<?php 
								foreach (array_values(NetUtils::getIcmpTypes()) as $icmpType)
									echo "<option value=\"$icmpType\">$icmpType</option>\n";
							?>
							</select>
						</td>
					</tr>
					<tr>
						<!-- Source Address -->
						<td class="tabInputLabel">Source Address:</td>
						<td class="tabInputValue"><input type="text" id="srcAddr" name="srcAddr" size="20" maxlength="20" /></td>
					</tr>
					<tr>
						<!-- Source Port -->
						<td class="tabInputLabel">Source Port:</td>
						<td class="tabInputValue"><input type="text" id="srcPort" name="srcPort" size="20" maxlength="5" /></td>
					</tr>
					<tr>
						<!-- Destination Address -->
						<td class="tabInputLabel">Destination Address:</td>
						<td class="tabInputValue"><input type="text" id="dstAddr" name="dstAddr" size="20" maxlength="20" /></td>
					</tr>
					<tr>
						<!-- Destination Port -->
						<td class="tabInputLabel">Destination Port:</td>
						<td class="tabInputValue"><input type="text" id="dstPort" name="dstPort" size="20" maxlength="5" /></td>
					</tr>
					<tr>
						<!-- Connection State -->
						<td class="tabInputLabel">Connection State:</td>
						<td class="tabInputValue">
							<?php 
								foreach (NetUtils::getConnectionStates() as $connState)
									echo "<input type=\"checkbox\" class=\"connState\" value=\"$connState\" />$connState&nbsp;\n";
							?>
						</td>
					</tr>
					<tr>
						<!-- Fragmented -->
						<td class="tabInputLabel">Fragmented:</td>
						<td class="tabInputValue">
							<select id="fragmented">
								<option value="any" checked>any</option>
								<option value="Y">Yes</option>
								<option value="N">No</option>
							</select>
						</td>
					</tr>
					<tr>
						<!-- Target -->
						<td class="tabInputLabel">Target:</td>
						<td class="tabInputValue">
							<select id="target">
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
							<button id="saveRuleBtn" type="button">Save</button>
							<button id="saveAsNewBtn" type="button">Save As New</button>
							<button id="deleteBtn" type="button">Delete</button>
							<button id="cancelBtn" type="button">Cancel</button>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
</body>
</html>