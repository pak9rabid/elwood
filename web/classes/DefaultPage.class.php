<?php
	require_once "Page.class.php";
	
	class DefaultPage implements Page
	{				
		// Override
		public function getName()
		{
			return "Default Page";
		}
		
		// Override
		public function headOut()
		{
			return "";
		}
		
		// Override
		public function contentOut()
		{
			return	"This is the default page.  You're seeing this page becaue " .
					"a page wasn't specified, or the specified page doesn't exist";
		}
		
		// Override
		public function isRestricted()
		{
			return false;
		}
	}
?>