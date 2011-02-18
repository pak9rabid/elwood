<?php
	require_once "User.class.php";
	require_once "Page.class.php";
	
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
				<a href="accessControl.php?logout=true">Logout</a>
END;
			}
				
			return $out .= <<<END
			
			</font>
				$title
END;
		}
		
		public static function navigation(Page $selectedPage)
		{
			$pages = array("Status", "Access", "WAN", "LAN", "Wireless", "Firewall", "NAT");
			$out = "";
			
			foreach ($pages as $page)
			{
				$cssClass = $page == $selectedPage->id() ? "selected" : "";
				$out .= <<<END
				
				<a class="$cssClass" href="elwoodPage.php?page=$page">$page</a>
END;
			}

			return $out;
			
		}
		
		public static function subPages(Page $parentPage, Page $selectedPage, array $subPages, array $parameters)
		{
			$out = <<<END
			
			<div style="margin: 15px">
				<div class="tab-panel">
END;

			foreach ($subPages as $subPage)
			{
				if (!($subPage instanceof Page))
					throw new Exception("The specified page object does not implement the Page interface");
					
				$cssClass = ($subPage === $selectedPage ? "tab-selected" : "tab");
					
				$out .= <<<END
				
					<a class="$cssClass" href="elwoodPage.php?page={$parentPage->id()}&tab={$subPage->id()}">{$subPage->name()}</a>
END;
			}
			
			$out .= <<<END
			
				</div>
				<div class="tab-content">
					{$selectedPage->content($parameters)}
				</div>
			</div>
			
END;
			return $out;
		}
	}
?>