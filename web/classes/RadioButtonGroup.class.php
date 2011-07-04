<?php
	require_once "ComboBox.class.php";
	
	class RadioButtonGroup extends ComboBox
	{
		public function __construct($name = "", array $options = array())
		{
			parent::__construct($name, $options);
		}
		
		// Override
		public function content()
		{
			$out = "";
			
			foreach ($this->getOptions() as $label => $value)
			{
				$out .=	"<input " .
							"type=\"radio\" " .
							"name=\"" . $this->getName() . "\" " .
							"id=\"" . ($this->getName() . $value) . "\" " .
							"class=\"elwoodInput " . (empty($this->classes) ? "" : $this->classesOut()) . "\" " .
							(empty($this->styles) ? "" : "style=\"" . $this->stylesOut() . "\" ") .
							"value=\"$value\" ".
							($value == $this->getValue() ? "checked=\"checked\" " : "") .
						">&nbsp;$label<br>";
			}
			
			return $out;
		}
	}
?>