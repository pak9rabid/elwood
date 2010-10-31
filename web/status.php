<?php
	require_once "accessControl.php";
	require_once "PageElements.class.php";
	require_once "NetworkSettings.class.php";
	require_once "RouterStats.class.php";
		
	$networkSettings = new NetworkSettings();
?>

<html>
<head>
	<title>Router Status</title>
	<link rel="StyleSheet" type="text/css" href="css/routerstyle.css">
	<link rel="StyleSheet" type="text/css" href="css/jquery.countdown.css">
	<script src="js/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="js/jquery.countdown.pack.js" type="text/javascript"></script>
	<script type="text/javascript">
		$(document).ready(function()
		{
			$("#uptime").countdown	(	{
											since: -<?=RouterStats::getUptime()?>,
											format: "dHMS",
											layout: "{d<}{dn} days and {d>}{hn}:{mnn}:{snn}"
										}
									);
		});
	</script>
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Status")?>
		<?=PageElements::navigationOut()?>
		<div id="content">	
			<table class="status-table" style="width: 60%;">
				<tr><th>WAN IP Address:</th><td><?=$networkSettings->getWanInterface()->getIp()?></td></tr>
				<tr><th>LAN IP Address:</th><td><?=$networkSettings->getLanInterface()->getIp()?></td></tr>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
				
<?php foreach ($networkSettings->getNameservers() as $key => $nameserver) { ?>
				<tr><th>Nameserver <?=$key + 1?>:</th><td><?=$nameserver?></td></tr>
<?php } ?>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
				<tr><th>Uptime:</th><td id="uptime"></td></tr>
			</table>
		</div>
	</div>
</body>
</html>
