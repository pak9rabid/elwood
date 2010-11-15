<?php
	interface Page
	{
		public function getName();
		public function headOut();
		public function contentOut();
		public function popupsOut();
		public function isRestricted();
	}
?>