<?php
	require_once "InputElement.class.php";
	
	class TextField extends InputElement
	{
		protected $maxLength;
		
		public function __construct($name = "", $value = "")
		{
			$this->setName($name);
			$this->setValue($value);
		}
		
		public function setMaxLength($maxLength = "")
		{
			if (!preg_match("/^[0-9]*$/", $maxLength))
				throw new Exception("Invalid max length specified");
				
			$this->maxLength = $maxLength;
		}
		
		public function getMaxLength()
		{
			return $this->maxLength;
		}
		
		// Override
		public function content()
		{
			return	"<input " .
						"type=\"text\" " .
						(empty($this->styles) ? "" : "style=\"" . $this->stylesOut() . "\" ") .
						"name=\"" . $this->getName() . "\" " .
						(empty($this->title) ? "" : "title=\"" . $this->getTitle() . "\" ") .
						"id=\"" . $this->getName() . "\" " .
						"class=\"elwoodInput textfield " . $this->classesOut() . "\" " . 
						(empty($this->maxLength) ? "" : "maxlength=\"" . $this->maxLength . "\"") .
						" value=\"" . $this->getValue() . "\">";
		}
	}
?>