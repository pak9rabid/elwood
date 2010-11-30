<?php
	require_once "SystemProfile.class.php";
	
	abstract class Service
	{
		protected $service;
		
		public static function getInstance($serviceName)
		{
			$profile = SystemProfile::getProfile();
			$service = $profile->services->$serviceName;
			
			if (empty($service))
				throw new Exception("Service $serviceName does not exist in profile " . $profile->name);
				
			$className = $service->class;
			
			if (empty($className))
				throw new Exception("Class $className does not exist in service $serviceName in profile " . $profile->name);
				
			require_once "$className.class.php";
			
			$obj = new $className();
			$obj->setService($service);
			
			return $obj;
		}
		
		public function getService()
		{
			return $this->service;
		}
		
		public function setService($service)
		{
			$this->service = $service;
		}
		
		abstract public function stop();
		abstract public function start();
		abstract public function restart();
		abstract public function save();
		abstract public function load();
		abstract public function isRunning();
	}
?>