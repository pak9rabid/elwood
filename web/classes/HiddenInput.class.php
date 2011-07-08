<?php
	require_once "InputElement.class.php";
	
	class HiddenInput extends InputElement
	{
		public function __construct($name = "", $value = "")
		{
			$this->setName($name);
			$this->setValue($value);
			
			$this->setAttribute("type", "hidden");
			$this->setAttribute("value", $value);
		}
		
		public function content()
		{			
			return "<input " . $this->attributesOut() . ">";
		}
	}
?>