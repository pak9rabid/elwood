<?php
	interface FwFilterTranslator
	{
		public function setDbFromSystem();
		public function setSystemFromDb($writeChanges);
	}
?>