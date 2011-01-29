<?php
	require_once "User.class.php";
	
	class PageSections
	{
		public static function title($title)
		{
			$user = User::getUser();
			
			$out = <<<END
			
			<font id="usertxt">
END;
			
			if (!empty($user))
			{
				$out .= <<<END
				Logged in: {$user->getAttribute("username")}<br />
				<a href="inc/accessControl.php?logout=true">Logout</a>
END;
			}
				
			return $out .= <<<END
			
			</font>
				$title
END;
		}
		
		public static function navigation(Page $page)
		{
			return	<<<END
			
			<a id="statusPageLink" href="elwoodPage.php?page=Status">Status</a>
			<a id="accessPageLink" href="elwoodPage.php?page=Access">Access</a>
			<a id="wanPageLink" href="elwoodPage.php?page=Wan">WAN</a>
			<a id="lanPageLink" href="elwoodPage.php?page=Lan">LAN</a>
			<a id="wirelessPageLink" href="elwoodPage.php?page=Wireless">Wireless</a>
			<a id="firewallPageLink" href="elwoodPage.php?page=Firewall">Firewall</a>
END;
		}
	}
?>