<?php
	require_once "accessControl.php";
	require_once "PageElements.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	
	$wanInt = NetworkInterface::getInstance("wan");
	$dns = new DNSSettings();
?>

<html>
<head>
	<title>WAN Setup</title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css">
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/wan.js.php" type="text/javascript"></script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("WAN Setup")?>
		<?=PageElements::navigationOut()?>
		<div id="content">
			<table class="ip-table" style="width: 400px;">
				<tr><th colspan="3">IP Address</th></tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="ipTypeDhcp" name="ipType" value="dynamic" /></td>
					<td align="left" colspan="2">Obtain IP address automatically</td>
				</tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="ipTypeStatic" name="ipType" value="static" /></td>
					<td align="left" colspan="2">Specify IP address</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">IP Address:</td>
					<td align="left"><input class="wanInput textfield staticIpSetting" size="20" maxlength="15" id="ipAddress" name="ipAddress" value="<?=$wanInt->getIp()?>" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">Subnet Mask:</td>
					<td align="left"><input class="wanInput textfield staticIpSetting" size="20" maxlength="15" id="netmask" name="netmask" value="<?=$wanInt->getNetmask()?>" /></td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="right">Gateway:</td>
					<td align="left"><input class="wanInput textfield staticIpSetting" size="20" maxlength="15" id="gateway" name="gateway" value="<?=$wanInt->getGateway()?>" /></td>
				</tr>
			</table>
			<br />
			<table id="dns-table" class="ip-table" style="width: 400px;">
				<tr><th colspan="3">DNS</th></tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="dnsTypeDhcp" name="dnsType" value="dynamic" /></td>
					<td align="left" colspan="2">Obtain DNS information automatically</td>
				</tr>
				<tr>
					<td align="right"><input class="wanInput" type="radio" id="dnsTypeStatic" name="dnsType" value="static" /></td>
					<td align="left">Specify DNS information</td>
					<td align="right"><button type="button" id="addNsBtn" title="Add nameserver">+</button></td>
				</tr>
<?php foreach ($dns->getNameservers() as $key => $nameserver) { ?>
				<tr>
					<td>&nbsp;</td>
					<td align="right">Nameserver <?=$key + 1?>:</td>
					<td><input class="wanInput textfield nameserverInput" id="nameserver<?=$key + 1?>" name="nameserver<?=$key + 1?>" value="<?=$nameserver?>" /></td>
				</tr>
<? } ?>
			</table>
			<br />
			<table class="access-table" style="width: 400px;">
				<tr><th>MTU</th></tr>
				<tr><td><input class="wanInput textfield" size="4" maxlength="10" id="mtu" name="mtu" value="<?=$wanInt->getMtu()?>" /></td></tr>
			</table>
			<button type="button" id="saveWanSettingsBtn" style="margin-top: 5px;">Save</button>
			<br />
			<div id="wanMessages"></div>
		</div>
	</div>
</body>
</html>