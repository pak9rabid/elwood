<?php
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	require_once "RouterStats.class.php";
	
	class StatusPage extends Page
	{
		// Override
		public function id()
		{
			return "Status";
		}
		
		// Override
		public function name()
		{
			return "Status";
		}
		
		// Override
		public function head(array $parameters)
		{
			return <<<END
			
			<rel="StyleSheet" type="text/css" href="css/jquery.countdown.css">
			<script src="js/jquery.countdown.pack.js" type="text/javascript"></script>
END;
		}
		
		// Override
		public function style(array $parameters)
		{
			
			return <<<END
			
			.status-table
			{
				background-color: #D0D0D0;
				margin-left: auto;
				margin-right: auto;
				border: 0px;
				border-collapse: collapse;
			}

			.status-table th
			{
				background-color: #A8A8A8;
				text-align: right;
			}

			.status-table td
			{
				text-align: center;
			}
END;
		}
		
		// Override
		public function javascript(array $parameters)
		{
			$uptime = RouterStats::getUptime();
			
			return parent::javascript($parameters) . <<<END
			
			$(function()
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
			$wanIp = NetworkInterface::getInstance("WAN")->getAddress();
			$lanIp = NetworkInterface::getInstance("LAN")->getAddress();
			
			$out = <<<END
			
			<table class="status-table" style="width: 60%;">
				<tr><th>WAN IP Address:</th><td>$wanIp</td></tr>
				<tr><th>LAN IP Address:</th><td>$lanIp</td></tr>
				<tr><th>&nbsp;</th><td>&nbsp;</td></tr>
END;

			foreach (DNSSettings::getNameservers() as $key => $nameserver)
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