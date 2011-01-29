<?php
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	require_once "RouterStats.class.php";
	
	class StatusPage implements Page
	{
		// Override
		public function id()
		{
			return "status";
		}
		
		// Override
		public function name()
		{
			return "Status";
		}
		
		// Override
		public function head()
		{
			return <<<END
			
			<rel="StyleSheet" type="text/css" href="css/jquery.countdown.css">
			<script src="js/jquery.countdown.pack.js" type="text/javascript"></script>
END;
		}
		
		// Override
		public function style()
		{
		}
		
		// Override
		public function javascript()
		{
			$uptime = RouterStats::getUptime();
			
			return <<<END
			
			$(document).ready(function()
			{	
				$("#uptime").countdown	(	{
												since: -$uptime,
												format: "dHMS",
												layout: "{d<}{dn} days and {d>}{hn}:{mnn}:{snn}"
											}
										);
			});
END;
		}
		
		// Override
		public function content(array $parameters)
		{			
			$wanIp = NetworkInterface::getInstance("wan")->getIp();
			$lanIp = NetworkInterface::getInstance("lan")->getIp();
			$dns = new DNSSettings();
			
			$out = <<<END
			
			<table class="status-table" style="width: 60%;">
				<tr><th>WAN IP Address:</th><td>$wanIp</td></tr>
				<tr><th>LAN IP Address:</th><td>$lanIp</td></tr>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
END;

			foreach ($dns->getNameservers() as $key => $nameserver)
				$out .= "<tr><th>Nameserver " . ($key + 1) . ":</th><td>" . $nameserver . "</td></tr>\n";
				
			return $out .= <<<END
			
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
				<tr><th>Uptime:</th><td id="uptime"></td></tr>
			</table>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>