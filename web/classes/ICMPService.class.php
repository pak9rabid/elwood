<?php
	require_once "Service.class.php";
	require_once "FirewallRule.class.php";
	
	class ICMPService extends Service
	{
		// Override
		public function stop()
		{
		}
		
		// Override
		public function start()
		{
		}
		
		// Override
		public function restart()
		{
		}
		
		// Override
		public function isRunning()
		{
			return true;
		}
		
		// Override
		public function getDefaultAccessRules()
		{
			$defaultRule = new FirewallRule();
			$defaultRule->setAllAttributes(array	(
														"service_id" => $this->getAttribute("id"),
														"protocol" => "icmp",
														"target" => "ACCEPT"
													));
		}
	}
?>