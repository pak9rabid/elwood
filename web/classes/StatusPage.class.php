<?php
	require_once "Page.class.php";
	require_once "NetworkInterface.class.php";
	require_once "DNSSettings.class.php";
	require_once "RouterStats.class.php";
	
	class StatusPage implements Page
	{
		// Override
		public function getName()
		{
			return "Status";
		}
		
		// Override
		public function headOut()
		{
			return	"<rel='StyleSheet' type='text/css' href='css/jquery.countdown.css'>\n" .
					"<script src='js/jquery.countdown.pack.js' type='text/javascript'></script>\n" .
					"<script src='js/status.js.php' type='text/javascript'></script>\n";
		}
		
		// Override
		public function contentOut()
		{
			$wanInt = NetworkInterface::getInstance("wan");
			$lanInt = NetworkInterface::getInstance("lan");
			$dns = new DNSSettings();
			
			$out =	"<table class='status-table' style='width: 60%;'>\n" .
						"<tr><th>WAN IP Address:</th><td>" . $wanInt->getIp() . "</td></tr>\n" .
						"<tr><th>LAN IP Address:</th><td>" . $lanInt->getIp() . "</td></tr>\n" .
						"<tr><th>&nbsp;</th><td>&nbsp;</td></tr>";
				
			foreach ($dns->getNameservers() as $key => $nameserver)
				$out .= "<tr><th>Nameserver " . ($key + 1) . ":</th><td>" . $nameserver . "</td></tr>\n";

			$out .=		"<tr><th>&nbsp;</th><td>&nbsp;</td></tr>\n" .
						"<tr><th>Uptime:</th><td id='uptime'></td></tr>\n" .
					"</table>\n";
			
			return $out;
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>