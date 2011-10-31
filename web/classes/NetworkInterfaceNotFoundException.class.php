<?php
	class NetworkInterfaceNotFoundException extends Exception
	{
		public function __construct($message = "The specified network interface does not exist")
		{
			parent::__construct($message);
		}
	}
?>