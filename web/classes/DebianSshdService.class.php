<?php
	require_once "Service.class.php";
	require_once "SSHService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	require_once "NetUtils.class.php";
	
	class DebianSshdService extends Service implements SSHService
	{
		private $port;

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
		
		// Override
		public function isRunning()
		{
			return file_exists($this->service->pid);
		}
		
		// Override
		public function getPort()
		{
			return $this->port;
		}
		
		// Override
		public function setPort($port)
		{
			if (!NetUtils::isValidIanaPortNumber($port))
				throw new Exception("Invalid port number specified for SSH server");
				
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