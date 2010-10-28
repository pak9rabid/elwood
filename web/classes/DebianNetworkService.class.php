<?php
	require_once "Service.class.php";
	require_once "Console.class.php";
	
	class DebianNetworkService extends Service
	{
		// Override
		public function stop()
		{
			Console::execute("sudo /etc/init.d/networking stop");
		}
		
		// Override
		public function start()
		{
			Console::execute("sudo /etc/init.d/networking start");
		}
		
		// Override
		public function restart()
		{
			Console::execute("sudo /etc/init.d/networking restart");
		}
		
		// Override
		public function save()
		{
			/* N/A */
		}
		
		// Override
		public function load()
		{
			/* N/A */
		}
		
		// Override
		public function setPort($port)
		{
			/* N/A */
		}
		
		// Override
		public function getPort()
		{
			/* N/A */
		}
	}
?>