<?php
	interface Page
	{
		public function getName();
		public function headOut();
		public function contentOut();
		public function isRestricted();
	}
?>