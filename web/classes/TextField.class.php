<?php
	require_once "InputElement.class.php";
	
	class TextField extends InputElement
	{		
		public function __construct($name = "", $value = "")
		{
			$this->setName($name);
			$this->setValue($value);
			
			$this->addClass("elwoodInput");
			$this->addClass("textfield");
			$this->setAttribute("type", "text");
			$this->setAttribute("value", $this->getValue());
		}
				
		// Override
		public function content()
		{			
			return "<input " . $this->attributesOut() . ">";
		}
	}
?>