<?php
	interface Page
	{
		public function name();
		public function head();
		public function content();
		public function popups();
		public function isRestricted();
	}
?>