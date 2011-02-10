<?php
	interface Page
	{
		public function id();
		public function name();
		public function head(array $parameters);
		public function style(array $parameters);
		public function javascript(array $parameters);
		public function content(array $parameters);
		public function popups(array $parameters);
		public function isRestricted();
	}
?>