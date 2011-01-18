<?php
	require_once "Service.class.php";
	require_once "SSHService.class.php";
	require_once "Console.class.php";
	require_once "FileUtils.class.php";
	
	class DebianSshdService extends Service implements SSHService
	{
		public function __construct()
		{
			parent::__construct();
		}
		
		// Override
		public function stop()
		{
			if ($this->isRunning())
			{
				Console::execute("sudo /etc/init.d/ssh stop");
				sleep(3);
			}
		}
		
		// Override
		public function start()
		{
			if ($this->isRunning())
				return;
				
			try
			{
				Console::execute("sudo /etc/init.d/ssh start");
				sleep(3);
			}
			catch (Exception $ex)
			{
				throw new Exception("The sshd service failed to start");
			}
		}
		
		// Override
		public function restart()
		{
			try
			{
				Console::execute("sudo /etc/init.d/ssh restart");
				sleep(3);
			}
			catch (Exception $ex)
			{
				throw new Exception("The sshd service failed to restart");
			}
		}
		
		// Override
		public function save()
		{
			parent::save();
			
			$config = FileUtils::readFileAsArray($this->service->config);
			$config = array_filter($config, array("self", "configFilter"));
			
			foreach ($this->accessRules as $accessRule)			
				$config[] = "Port " . $accessRule->getAttribute("dport");
			
			FileUtils::writeToFile($this->service->config, implode("\n", $config));
		}
		
		// Override
		public function isRunning()
		{
			return file_exists($this->service->pid);
		}
		
		// Override
		public function getDefaultAccessRules()
		{
			$defaultRule = new FirewallRule();
			$defaultRule->setAllAttributes(array(
													"service_id" => $this->getAttribute("id"),
													"protocol" => "tcp",
													"dport" => 22
												));
												
			return array($defaultRule);
		}
						
		private static function configFilter($line)
		{
			// Filter out all the stuff we'll be replacing
			return !	(
							preg_match("/^Port.*$/", $line)	||
							preg_match("/^ListenAddress.*$/", $line)
						);
		}
	}
?>