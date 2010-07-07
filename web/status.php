<?php
	require_once "accessControl.php";
	require_once "PageElements.class.php";

	require_once "status.inc";
?>

<html>
<head>
	<title>Router Status</title>
	<link rel="StyleSheet" type="text/css" href="routerstyle.css">
</head>

<body>
	<div id="container">
		<?=PageElements::titleOut("Firewall")?>
		<?=PageElements::navigationOut()?>
		<div id="content">
		<?php
			# Get current router status
			$routerStatus = new routerStatus;

			# Display current router status
echo <<<END
			<table id="status-table">
				<tr><th>WAN IP Address:</th><td>$routerStatus->wanIP</td></tr>
				<tr><th>LAN IP Address:</th><td>$routerStatus->lanIP</td></tr>
				<tr><th>&nbsp</th><td>&nbsp</td></tr>
END;
			foreach ($routerStatus->dnsList as $key => $value)
			{
				$index = $key + 1;
				echo "<tr><th>Nameserver $index:</th><td>$value</td></tr>";
			}
echo <<<END
				<tr><th>&nbsp</th><td>&nbsp</td></tr>
				<tr><th>Uptime:</th><td>$routerStatus->uptime</td></tr>
				<tr><th>Active Interfaces:</th><td>
END;
			foreach ($routerStatus->activeIfacesList as $value)
				echo "$value ";

			echo "</td></tr>";
echo <<<END
			</table>
END;
	?>
		</div>
	</div>
</body>
</html>
