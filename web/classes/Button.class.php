<?php
	require_once "InputElement.class.php";
	
	class Button extends InputElement
	{
		public function __construct($name = "", $value = "")
		{
			$this->setName($name);
			$this->setValue($value);
		}
		
		// Override
		public function content()
		{
			return	"<button " .
						"type=\"button\" ". 
						"id=\"" . $this->getName() . "\" " .
						"name=\"" . $this->getName() . "\"" .
						(empty($this->styles) ? "" : " style=\"" . $this->stylesOut() . "\"") .
						(empty($this->classes) ? "" : " class=\"" . $this->classesOut() . "\"") .
					">" .
						$this->getValue() .
					"</button>";
		}
	}
?>