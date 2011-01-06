<?php
	class StringUtils
	{
		public static function beginsWith($str, $sub)
		{
			return (strncmp($str, $sub, strlen($sub)) == 0);
		}
	}
?>