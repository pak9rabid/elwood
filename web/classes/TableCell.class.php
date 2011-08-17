<?php
	require_once "Element.class.php";
	
	class TableCell extends Element
	{
		protected $content;
		
		public function __construct($name = "", $content = "")
		{
			$this->name = $name;
			$this->content = $content;
		}
		
		// Override
		public function content()
		{
			$attributes = $this->attributesOut();
			return "<td" . (!empty($attributes) ? " $attributes" : "") . ">" . $this->content . "</td>";
		}
	}
?>