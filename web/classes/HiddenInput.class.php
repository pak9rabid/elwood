<?php
	require_once "InputElement.class.php";
	
	class HiddenInput extends InputElement
	{
		public function __construct($name = "", $value = "")
		{
			$this->setName($name);
			$this->setValue($value);
		}
		
		public function content()
		{
			return <<<END
			
			<input type="hidden" name="{$this->getName()}" id="{$this->getName()}" value="{$this->getValue()}" />
END;
		}
	}
?>