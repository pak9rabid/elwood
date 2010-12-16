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
			try
			{
				Console::execute("sudo /etc/init.d/networking start");
			}
			catch (Exception $ex)
			{
				throw new Exception("The network service failed to start");
			}
		}
		
		// Override
		public function restart()
		{
			try
			{
				Console::execute("sudo /etc/init.d/networking restart");
			}
			catch (Exception $ex)
			{
				throw new Exception("The network service failed to restart");
			}
		}
		
		// Override
		public function save()
		{
			/*
			 * Network settings get saved on a per-interface bases via the
			 * NetworkInterface classes
			 */
		}
		
		// Override
		public function load()
		{
			/*
			 * Network settings get loaded on a per-interface bases via the
			 * NetworkInterface classes
			 */
		}
		
		// Override
		public function isRunning()
		{
			return true;
		}
	}
?>