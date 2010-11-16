<?php
	require_once "Page.class.php";
	
	class DefaultPage implements Page
	{				
		// Override
		public function name()
		{
			return "Default Page";
		}
		
		// Override
		public function head()
		{
		}
		
		// Override
		public function content()
		{
			return	"This is the default page.  You're seeing this page becaue " .
					"a page wasn't specified, or the specified page doesn't exist";
		}
		
		// Override
		public function popups()
		{
		}
		
		// Override
		public function isRestricted()
		{
			return false;
		}
	}
?>