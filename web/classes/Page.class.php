<?php
	interface Page
	{
		public function name();
		public function head();
		public function content(array $parameters);
		public function popups(array $parameters);
		public function isRestricted();
	}
?>