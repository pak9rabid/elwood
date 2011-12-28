<?php
	class SettingNotFoundException extends Exception
	{
		public function __construct($message = "The specified setting does not exist")
		{
			parent::__construct($message);
		}
	}
?>