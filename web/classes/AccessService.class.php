<?php
	require_once "Service.class.php";
	require_once "NetUtils.class.php";
	
	abstract class AccessService extends Service
	{
		protected $port;
		
		public function getPort()
		{
			return $this->port;
		}
		
		public function setPort($port)
		{
			$this->port = $port;
		}
	}
?>