<?php
	class AjaxResponse
	{
		protected $responseText;
		protected $hasError;
		
		public function __construct($responseText = "", $hasError = false)
		{
			$this->responseText = $responseText;
			$this->hasError = $hasError;
		}
		
		public function hasError()
		{
			return $this->hasError;
		}
		
		public function getResponseText()
		{
			return $this->responseText;
		}
		
		public function setError($hasError)
		{
			$this->hasError = $hasError;
		}
		
		public function setResponseText($responseText)
		{
			$this->responseText = $responseText;
		}
		
		public function toJson()
		{
			foreach ($this as $key => $value)
				$json->$key = $value;
				
			return json_encode($json);
		}
	}
?>