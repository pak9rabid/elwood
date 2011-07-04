<?php
	require_once "Element.class.php";
	
	abstract class InputElement extends Element
	{
		protected $label;
		protected $value;
		
		public function setLabel($label)
		{
			$this->label = $label;
		}
		
		public function setValue($value)
		{
			$this->value = $value;
		}
		
		public function getLabel()
		{
			return $this->label;
		}
		
		public function getValue()
		{
			return $this->value;
		}
	}
?>