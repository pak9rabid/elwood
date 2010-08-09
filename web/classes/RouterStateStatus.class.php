<?php
	require_once "DataHash.class.php";
	
	class RouterStateStatus extends DataHash
	{
		public function __construct()
		{
			parent::__construct("router_state_status");
			parent::setPrimaryKey("name");
		}
	}
?>