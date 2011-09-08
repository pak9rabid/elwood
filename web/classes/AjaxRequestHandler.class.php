<?php
	interface AjaxRequestHandler
	{
		public function processRequest(array $parameters);
		public function isRestricted();
	}
?>