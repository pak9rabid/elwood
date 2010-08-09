<?php
	class AjaxResponse
	{
		protected $responseText;
		protected $errors;
		
		public function __construct($responseText = "", array $errors = array())
		{
			$this->responseText = $responseText;
			$this->errors = $errors;
		}
		
		public function hasErrors()
		{
			return count($this->errors) > 0;
		}
		
		public function getResponseText()
		{
			return $this->responseText;
		}
		
		public function setErrors(array $errors)
		{
			$this->errors = $errors;
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