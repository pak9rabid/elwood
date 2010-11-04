<?php
	class FileUtils
	{
		public static function writeToFile($file, $output)
		{
			if (!$fp = @fopen($file, 'w'))
				throw new Exception("Unable to open file for writing: $file");
				
			fwrite($fp, $output);
			fclose($fp);
		}
		
		public static function readFileAsString($file)
		{
			$content = @file_get_contents($file);
			
			if (!$content)
				throw new Exception("Unable to read contents of file: $file");
				
			return $content;
		}
		
		public static function readFileAsArray($file)
		{
			$content = @file($file, FILE_IGNORE_NEW_LINES);
			
			if (!$content)
				throw new Exception("Unable to read contents of file: $file");
				
			return $content;
		}
	}
?>