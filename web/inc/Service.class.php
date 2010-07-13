<?php
	require_once "SystemProfile.class.php";
	
	abstract class Service
	{
		protected $service;
		
		public static function getClass($serviceName)
		{
			$profile = SystemProfile::getProfile();
			$service = $profile->services->$serviceName;
			
			if (empty($service))
				throw new Exception("Error: Service $serviceName does not exist in profile " . $profile->name);
				
			$className = $service->class;
			
			if (empty($className))
				throw new Exception("Error: Class $className does not exist in service $serviceName in profile " . $profile->name);
				
			require_once "$className.class.php";
			
			$obj = new $className();
			$obj->setService($service);
			
			return $obj;
		}
		
		public function getService()
		{
			return $service;
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
	}
?>