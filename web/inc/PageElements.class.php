<?php
	require_once "User.class.php";
	
	class PageElements
	{
		public static function titleOut($title)
		{
			$user = User::getUser();
			
			$out = "<div id='title'>\n";
			
			$out .= "\t<font id='usertxt'>\n";
			
			if (!empty($user))
			{
				$out .= "\t\tLogged in: " . $user->getAttribute("username") . "<br />\n" .
						"\t\t<a href='inc/accessControl.php?logout=true'>Logout</a>\n";
			}
			else
				$out .= "\t\t&nbsp;\n";
				
			$out .= "\t</font>\n" .
					"\t$title" .
					"</div>";
			
			return $out;
		}
		
		public static function navigationOut()
		{
			return	"<div id='navigation'>\n" .
					"	<a href='status.php'>Status</a>\n" .
					"	<a href='access.php'>Access</a>\n" .
					"	<a href='wan.php'>WAN</a>\n" .
					"	<a href='lan.php'>LAN</a>\n" .
					"	<a href='wifi.php'>WiFi</a>\n" .
					"	<a href='wol.php'>WOL</a>\n" .
					"	<a href='firewall.php'>Firewall</a>\n" .
					"	<a href='portforward.php'>Port Forwarding</a>\n" .
					"	<a href='webterm.php'>WebTerm</a>\n" .
					"</div>\n";
		}
		
		public static function hideShow($content)
		{
			return	"<div id='hideshow'>\n" .
					"	<div id='fade'></div>\n" .
					"	<div class='popup_block'>\n\n" .
					$content .
					"\n\n	</div>\n";
			
		}
	}
?>