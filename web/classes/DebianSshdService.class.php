<?php
	require_once "AccessService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	
	class DebianSshdService extends AccessService
	{		
		// Override
		public function stop()
		{
			Console::execute("sudo /etc/init.d/ssh stop");
		}
		
		// Override
		public function start()
		{
			Console::execute("sudo /etc/init.d/ssh start");
		}
		
		// Override
		public function restart()
		{
			Console::execute("sudo /etc/init.d/ssh restart");
		}
		
		// Override
		public function save()
		{			
			$configFile = file($this->service->config, FILE_IGNORE_NEW_LINES);
			$newConfigFile = array_map(array("self", "setPortCallback"), $configFile);
			FileUtils::writeToFile($this->service->config, implode("\n", $newConfigFile));
		}
		
		// Override
		public function load()
		{
			function filterPort($line)
			{
				return preg_match("/^Port.*$/", $line);
			}
			
			$lines = file($this->service->config, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
			$lines = array_filter($lines, "filterPort");
			
			if (empty($lines))
				throw new Exception("Port setting not found in sshd config file");
				
			list($temp, $port) = explode(" ", array_shift($lines));
			
			$this->port = $port;
		}
				
		private function setPortCallback($line)
		{
			if (preg_match("/^Port.*$/", $line))
				return "Port " . $this->port;
			else
				return $line;
		}
	}
?>