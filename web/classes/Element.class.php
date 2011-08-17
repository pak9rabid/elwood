<?php
	require_once "InputElement.class.php";
	
	abstract class Element
	{
		protected $name;
		protected $attributes = array();
		protected $classes = array();
		protected $styles = array();
		protected $eventHandlers = array();
		
		abstract public function content();
		
		public static function isValidLength($length)
		{
			return preg_match("/^[0-9]+(px|em|\%)$/", $length);
		}
		
		protected function attributesOut()
		{
			$out = (!empty($this->name) ? array("id=\"$this->name\"") : array());
						
			if (!empty($this->classes))
				$out[] = "class=\"" . $this->classesOut() . "\"";
				
			if (!empty($this->styles))
				$out[] = "style=\"" . $this->stylesOut() . "\"";
			
			foreach ($this->attributes as $name => $value)
				$out[] = "$name=\"$value\"";
				
			return implode(" ", $out);
		}
		
		// Override
		public function __toString()
		{
			return $this->content();
		}
						
		public function javascript()
		{
			if (empty($this->eventHandlers))
				return "";
				
			$out = array("$(function(){");
			
			foreach ($this->eventHandlers as $event => $handlers)
			{
				foreach ($handlers as $handler)
					$out[] = "$('#" . $this->getName() . "').bind('$event', $handler);\n";
			}
			
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
		
		public function getAttribute($attribute)
		{
			return $this->attributes[$attribute];
		}
		
		public function getAttributes()
		{
			return $this->attributes;
		}
		
		public function getClasses()
		{
			return $this->classes;
		}
		
		public function hasClass($className)
		{
			return in_array($className, $this->classes);
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
			if (!isset($this->eventHandlers[$event]))
				$this->eventHandlers[$event] = array();
				
			$this->eventHandlers[$event][] = $handler;
		}
		
		public function setAttribute($attribute, $value)
		{
			$attribute = strtolower($attribute);
			
			// blacklisted attributes...usually because there's a dedicated store for them
			$blacklist = array("id", "name", "class", "style", "value");
			
			if (in_array($attribute, $blacklist))
				throw new Exception("The specified attribute cannot be set");
				
			$this->attributes[$attribute] = $value;
		}
		
		public function addClass($class)
		{
			if (!in_array($class, $this->classes))
				$this->classes[] = $class;
		}
		
		function addClasses(array $classes)
		{
			$this->classes = array_merge($this->classes, $classes);
		}
		
		public function addStyle($attribute, $value)
		{
			$this->styles[$attribute] = $value;
		}
		
		public function addStyles(array $styles)
		{
			$this->styles = array_merge($this->styles, $styles);
		}
		
		public function removeHandler($event, $handler = "")
		{
			if (empty($handler))
				unset($this->eventHandlers[$event]);
			else
			{
				if ($index = @array_search($handler, $this->eventHandlers[$event]))
					unset($this->eventHandlers[$event][$index]);
			}
		}
		
		public function removeAttribute($attribute)
		{
			unset($this->attribute[$attribute]);
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
				
		public function cloneElementContent($elementName = "@@@CLONED_ELEMENT@@@", $escapeChars = true)
		{
			// typically used by javascript on the client to (somewhat) easily create copies of an
			// Element object's content
			$element = new $this($elementName);
			
			if ($element instanceof InputElement)
				$element->setValue($this->getValue());
				
			$content = $element->content();
			$content = str_replace(array("\r", "\r\n", "\n", "\t"), "", $content);
			
			return $escapeChars ? addslashes($content) : $content;
		}
		
		private function classesOut()
		{
			return (empty($this->classes) ? "" : implode(" ", $this->classes));
		}
		
		private function stylesOut()
		{
			if (empty($this->styles))
				return "";
				
			$out = array();
			
			foreach ($this->styles as $attribute => $value)
				$out[] = "$attribute: $value;";
				
			return implode(" ", $out);
		}
	}
?>