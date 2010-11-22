<?php
	require_once "AccessService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	
	class DebianApache2Service extends AccessService
	{
		// Override
		public function stop()
		{
			Console::execute("sudo /etc/init.d/apache2 stop");
		}
		
		// Override
		public function start()
		{
			Console::execute("sudo /etc/init.d/apache2 start");
		}
		
		// Override
		public function restart()
		{
			try
			{
				Console::execute("sudo /etc/init.d/apache2 reload");
			}
			catch (Exception $ex)
			{
				// Compensating for a bug when running '/etc/init.d/apache2 reload' where
				// it returns an exit status of 1, even though the command runs ok
			}
		}
		
		// Override
		public function save()
		{
			FileUtils::writeToFile($this->service->config, "Listen " . $this->port);
		}
		
		// Override
		public function load()
		{
			list($temp, $port) = explode(" ", file_get_contents($this->service->config));
			$this->port = $port;
		}
	}
?>