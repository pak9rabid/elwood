<?php
	require_once "User.class.php";
	require_once "Page.class.php";
	
	class PageSections
	{
		public static function title($title)
		{
			$user = User::getUser();
			$out = <<<END
			
			<div style="height: 30px; font-size: 12px; color: #6495ED; text-align: right;">
END;

			if (!empty($user))
			{
				$out .= <<<END
				
				User: $user<br>
				<a style="color: red;" href="pageRequest.php?page=Login">Logout</a>
END;
			}
				
			return $out .= <<<END
			
			</div>
				$title
END;
		}
		
		public static function navigation(Page $selectedPage)
		{
			$pages = array("Status", "Access", "WAN", "LAN", "Wireless", "Firewall", "NAT", "Setup");
			$out = "";
			
			foreach ($pages as $page)
			{
				$cssClass = $page == $selectedPage->id() ? "selected" : "";
				$out .= <<<END
				
				<a class="$cssClass" href="pageRequest.php?page=$page">$page</a>
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
				
					<a class="$cssClass" href="pageRequest.php?page={$parentPage->id()}&tab={$subPage->id()}">{$subPage->name()}</a>
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