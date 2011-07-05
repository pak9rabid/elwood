<?php
	require_once "InputElement.class.php";
	
	class ComboBox extends InputElement
	{
		protected $options = array();
		
		public function __construct($name = "", array $options = array())
		{
			$this->setName($name);
			$this->setOptions($options);
		}
		
		public function getOptions()
		{
			return $this->options;
		}
	
		public function setOptions(array $options)
		{
			$this->options = $options;
		}
		
		public function addOption($label, $value)
		{
			$this->options[$label] = $value;
		}
		
		public function clearOptions()
		{
			$this->options = array();
		}
		
		// Override
		public function content()
		{
			$out =	"<select " .
						"name=\"" . $this->getName() . "\" " .
						(empty($this->title) ? "" : " title=\"" . $this->getTitle() . "\" ") .
						"id=\"" . $this->getName() . "\" " .
						"class=\"elwoodInput" . (empty($this->classes) ? "" : " " . $this->classesOut()) . "\" " .
						(empty($this->styles) ? "" : "style=\"" . $this->stylesOut() . "\"") .
					">";
			
			foreach ($this->options as $label => $value)
				$out .= "<option value=\"$value\"" . ($value == $this->getValue() ? " selected=\"selected\"" : "") . ">$label</option>";
				
			return $out . "</select>";
		}
	}
?>