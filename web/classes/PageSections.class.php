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
		
		public static function navigation()
		{
			return	<<<END
			
			<a href="elwoodPage.php?page=Status">Status</a>
			<a href="elwoodPage.php?page=Access">Access</a>
			<a href="elwoodPage.php?page=Wan">WAN</a>
			<a href="elwoodPage.php?page=Lan">LAN</a>
			<a href="elwoodPage.php?page=Wireless">Wireless</a>
			<a href="elwoodPage.php?page=Firewall">Firewall</a>
END;
		}
	}
?>