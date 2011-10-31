<?php
	class NetworkInterfaceAlreadyUsedException extends Exception
	{
		public function __construct($message = "The specified interface is already used")
		{
			parent::__construct($message);
		}
	}
?>