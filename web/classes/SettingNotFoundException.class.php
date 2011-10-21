<?php
	class SettingNotFoundException extends Exception
	{
		public function __construct()
		{
			parent::__construct("The specified setting does not exist");
		}
	}
?>