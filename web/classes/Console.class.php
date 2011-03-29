<?php
	class Console
	{
		public static function execute($command, $ignoreErrors = false)
		{
			$output = array();
			$line = exec($command, $output, $exitCode);

			if (!$ignoreErrors && $exitCode != 0)
				throw new Exception("Error encountered when running command: $command");
			
			return $output;
		}
	}
?>