<?php
	require_once "RouterSettings.class.php";
	require_once "IPTablesFwFilterTranslator.class.php";
	
	// Provides dependency injection to provide support for multiple platforms
	// System profile is specified in the 'settings' table of the elwood db
	// Currently, only the 'Debian4' profile is supported and implemented
	class ClassFactory
	{
		private static function getSystemProfile()
		{
			return RouterSettings::getSettingValue("SYSTEM_PROFILE");
		}
		
		public static function getFwFilterTranslator()
		{
			return new IPTablesFwFilterTranslator();
		}
	}
?>