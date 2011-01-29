<?php
	interface Page
	{
		public function id();
		public function name();
		public function head();
		public function style();
		public function javascript();
		public function content(array $parameters);
		public function popups(array $parameters);
		public function isRestricted();
	}
?>