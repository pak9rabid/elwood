<?php
	require_once "Service.class.php";
	require_once "HTTPService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	require_once "NetUtils.class.php";
	
	class DebianApache2Service extends Service implements HTTPService
	{
		private $port;
		
		// Override
		public function stop()
		{
			if ($this->isRunning())
			{
				Console::execute("sudo /etc/init.d/apache2 stop");
				sleep(3);
			}
		}
		
		// Override
		public function start()
		{
			if (!$this->isRunning())
			{
				Console::execute("sudo /etc/init.d/apache2 start");
				sleep(3);
			}
		}
		
		// Override
		public function restart()
		{
			if (!$this->isRunning())
				$this->start();
			else
			{
				try
				{
					Console::execute("sudo /etc/init.d/apache2 reload");
				}
				catch (Exception $ex)
				{
					/* HACK: For some reason, when issuing '/etc/init.d/apache2 reload' from within PHP,
					 * it gets an exit code of 1 back, signifiying an error, when there were no errors.
					 * This is a hacky workaround for that situation.  Be aware that this may cause legitimate
					 * errors to go unnoticed.
					 */
				}
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
				throw new Exception("Invalid port number specified for HTTP server");
				
			$this->port = $port;
		}
	}
?>