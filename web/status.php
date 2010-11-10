<?php
	require_once "accessControl.php";
	require_once "PageElements.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	require_once "RouterStats.class.php";

	$wanInt = NetworkInterface::getInstance("wan");
	$lanInt = NetworkInterface::getInstance("lan");
	$dns = new DNSSettings();
?>

<html>
<head>
	<title>Router Status</title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css">
	<link rel="StyleSheet" type="text/css" href="css/jquery.countdown.css">
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.countdown.pack.js" type="text/javascript"></script>
	<script src="js/status.js.php" type="text/javascript"></script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Status")?>
		<?=PageElements::navigationOut()?>
		<div id="content">	
			<table class="status-table" style="width: 60%;">
				<tr><th>WAN IP Address:</th><td><?=$wanInt->getIp()?></td></tr>
				<tr><th>LAN IP Address:</th><td><?=$lanInt->getIp()?></td></tr>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
				
<?php foreach ($dns->getNameservers() as $key => $nameserver) { ?>
				<tr><th>Nameserver <?=$key + 1?>:</th><td><?=$nameserver?></td></tr>
<? } ?>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
				<tr><th>Uptime:</th><td id="uptime"></td></tr>
			</table>
		</div>
	</div>
</body>
</html>
