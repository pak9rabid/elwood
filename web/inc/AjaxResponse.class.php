<?php
	class AjaxResponse
	{
		protected $hasError;
		protected $responseText;
		
		public function __construct($hasError = false, $responseText = "")
		{
			$this->hasError = $hasError;
			$this->responseText = $responseText;
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