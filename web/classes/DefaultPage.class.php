<?php
	require_once "Page.class.php";
	
	class DefaultPage extends Page
	{	
		// Override
		public function id()
		{			
			return "default";
		}
		
		// Override
		public function name()
		{
			return "Default Page";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
		}
		
		// Override
		public function javascript(array $parameters)
		{
		}
		
		// Override
		public function content(array $parameters)
		{
			return	"This is the default page.  You're seeing this page becaue " .
					"a page wasn't specified, or the specified page doesn't exist";
		}
		
		// Override
		public function popups(array $parameters)
		{
		}
		
		// Override
		public function isRestricted()
		{
			return false;
		}
	}
?>