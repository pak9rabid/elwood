<?php
	class Console
	{
		public static function execute($command)
		{
			$output = array();
			$line = exec($command, $output, $exitCode);

			if ($exitCode != 0)
				throw new Exception("Error encountered when running command: $command");
			
			return $output;
		}
	}
?>