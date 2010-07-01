<?php
	class FileUtils
	{
		public static function writeToFile($file, $output)
		{
			if (!$fp = fopen($file, 'w'))
				throw new Exception("Error: Unable to open file for writing: $file");
				
			fwrite($fp, $output);
			fclose($fp);
		}
	}
?>