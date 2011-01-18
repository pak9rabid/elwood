<?php
	require_once "DataHash.class.php";
	require_once "SystemProfile.class.php";
	require_once "FirewallRule.class.php";
	
	abstract class Service extends DataHash
	{
		protected $service;
		protected $accessRules = array();
		
		public function __construct()
		{
			parent::__construct("services");
		}
		
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
			
			if (!$obj instanceof self)
				throw new Exception("$className is not a subclass of Service");
			
			$obj->setService($service);
			$obj->setAttribute("service_name", $serviceName);
			
			return $obj;
		}
		
		public static function getRegisteredServices()
		{
			$profile = SystemProfile::getProfile();
			
			$services = array();
			
			foreach ($profile->services as $serviceName => $service)
				$services[] = self::getInstance($serviceName);
				
			return $services;
		}
		
		public function getService()
		{
			return $this->service;
		}
				
		public function setService($service)
		{
			$this->service = $service;
		}
		
		public function getAccessRules()
		{
			return $this->accessRules;
		}
			
		public function addAccessRule(FirewallRule $accessRule)
		{
			$accessRule = clone $accessRule;
			$accessRule->setAttribute("service_id", $this->getAttribute("id"));
			$this->accessRules[] = $accessRule;
		}
		
		public function clearAccessRules()
		{
			$this->accessRules = array();
		}
		
		public function load()
		{
			foreach ($this->executeSelect() as $resultHash)
				$this->setAllAttributes($resultHash->getAttributeMap());
			
			$selectHash = new FirewallRule();
			$selectHash->setAttribute("service_id", $this->getAttribute("id"));
			
			$this->accessRules = $selectHash->executeSelect(true);
		}
		
		public function save()
		{
			$this->executeUpdate();
		}
		
		abstract public function stop();
		abstract public function start();
		abstract public function restart();
		abstract public function isRunning();
		abstract public function getDefaultAccessRules();
	}
?>