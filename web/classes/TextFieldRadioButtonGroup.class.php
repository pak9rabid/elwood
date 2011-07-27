<?php
	require_once "RadioButtonGroup.class.php";
	require_once "TextField.class.php";
	
	class TextFieldRadioButtonGroup extends RadioButtonGroup
	{
		public function __construct($name = "", array $options = array())
		{
			$this->setName($name);			
			$this->setOptions($options);
			$this->setAttribute("type", "radio");
			$this->addClass("elwoodInput");
		}
		
		// Override
		public function setOptions(array $options)
		{
			foreach ($options as $option)
			{
				if (!($option instanceof TextField))
					throw new Exception("Invalid type...must be of type TextField");
			}
			
			$this->options = $options;
		}
		
		// Override
		public function addOption(TextField $option)
		{
			$this->options[] = $option;
		}
		
		// Override
		public function content()
		{
			$out = "";
			$index = 0;
			
			foreach ($this->getOptions() as $option)
			{
				$id = $this->getName() . $index;
				$attributes = $this->attributesOut();
				$attributes = preg_replace("/id=\"([^\"]*)\"/", "id=\"$id\"", $attributes);
				
				if (preg_match("/value=/", $attributes))
					$attributes = preg_replace("/value=\"([^\"]*)\"/", "value=\"$index\"", $attributes);
				else
					$attributes .= " value=\"" . $index . "\"";
					
				if ($this->getValue() == $option->getValue())
					$attributes .= " checked=\"checked\"";
					
				$out .= "<div id=\"$id-container\"><input $attributes>&nbsp;$option</div>";
				
				$index++;
			}
			
			return $out;
		}
	}
?>