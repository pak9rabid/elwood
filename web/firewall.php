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
		var xhr = getXmlHttpRequest();

		if (!xhr)
			return;

		// Get order of the firewall rules
		var table = document.getElementById("firewall-table");
		var rules = new Array();
		    	
		for (i=0 ; i<table.rows.length ; i++)
		{
			if (table.rows[i].id)
			rules.push(table.rows[i].id);
		}

		xhr.onreadystatechange = function()
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

		xhr.open("GET", "ajax/editFwFilterRules.php?dir=<?=$direction?>&order=" + rules, true);
		xhr.send();
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
			<div id="fwTable">
				<?php $fwFilter->out($direction); ?>
			</div>
			<div id="fwActions">&nbsp;</div>
		</div>
	</div>
</body>
</html>