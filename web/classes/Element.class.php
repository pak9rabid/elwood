<?php
	abstract class Element
	{
		protected $name;
		protected $eventHandlers = array();
		protected $classes = array();
		protected $styles = array();
		
		abstract public function content();
		
		public static function isValidLength($length)
		{
			return preg_match("/^[0-9]+(px|em|\%)$/", $length);
		}
		
		protected function classesOut()
		{
			return (empty($this->classes) ? "" : implode(" ", $this->classes));
		}
		
		protected function stylesOut()
		{
			if (empty($this->styles))
				return "";
				
			$out = array();
			
			foreach ($this->styles as $attribute => $value)
				$out[] = "$attribute: $value;";
				
			return implode(" ", $out);
		}
				
		public function javascript()
		{
			if (empty($this->eventHandlers))
				return "";
				
			$out = array("$(function(){");
			
			foreach ($this->eventHandlers as $event => $handler)
				$out[] = "$('#" . $this->getName() . "').bind('$event', $handler);\n";
			
			$out[] = "});\n";
			return implode("\n", $out);
		}
		
		public function getName()
		{
			return $this->name;
		}
				
		public function getHandlers()
		{
			return $this->eventHandlers;
		}
		
		public function getClasses()
		{
			return $this->classes;
		}
		
		public function getStyle($attribute)
		{
			return $this->styles[$attribute];
		}
		
		public function getStyles()
		{
			return $this->styles;
		}
		
		public function setName($name)
		{
			if (empty($name))
				throw new Exception("No name specified");
				
			$this->name = $name;
		}
		
		public function setHandlers(array $eventHandlers)
		{
			$this->eventHandlers = $eventHandlers;
		}
		
		public function setClasses(array $classes)
		{
			$this->classes = $classes;
		}
		
		public function setStyles(array $styles)
		{
			$this->styles = $styles;
		}
		
		public function addHandler($event, $handler)
		{
			$this->eventHandlers[$event] = $handler;
		}
		
		public function addClass($class)
		{
			if (!in_array($class, $this->classes))
				$this->classes[] = $class;
		}
		
		public function addStyle($attribute, $value)
		{
			$this->styles[$attribute] = $value;
		}
		
		public function removeHandler($event)
		{
			unset($this->eventHandlers[$event]);
		}
		
		public function removeClass($rmClass)
		{
			foreach ($this->classes as $key => $class)
			{
				if ($class == $rmClass)
					unset($this->classes[$key]);
			}
		}
		
		public function removeStyle($attribute)
		{
			unset($this->styles[$attribute]);
		}
		
		public function clearHandlers()
		{
			$this->eventHandlers = array();
		}
	}
?>