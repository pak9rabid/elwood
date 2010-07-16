<?php
	interface AjaxRequestHandler
	{
		public function processRequest(array $parameters);
		public function getResponse();
	}
?>