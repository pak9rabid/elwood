<?php
	require_once "InputElement.class.php";
	
	class CheckBox extends InputElement
	{
		protected $isSelected = false;
		
		public function __construct($name = "", $isSelected = false)
		{
			$this->setName($name);
			$this->setSelected($isSelected);
		}
		
		public function isSelected()
		{
			return $this->isSelected;
		}
		
		public function setSelected($isSelected)
		{
			$this->isSelected = (boolean)$isSelected;
		}
		
		// Override
		public function content()
		{
			return	"<input " .
						"type=\"checkbox\" " .
						"name=\"" . $this->getName() . "\" " .
						(empty($this->title) ? "" : " title=\"" . $this->getTitle() . "\" ") .
						"id=\"" . $this->getName() . "\" " .
						"class=\"elwoodInput" . (empty($this->classes) ? "" : $this->classesOut()) . "\" " .
						(empty($this->styles) ? "" : "style=\"" . $this->stylesOut() . "\" ") .
						($this->isSelected ? "checked=\"checked\"" : "") .
					">";
		}
	}
?>