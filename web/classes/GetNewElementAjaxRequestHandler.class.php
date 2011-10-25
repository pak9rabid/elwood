<?php
	require_once "AjaxRequestHandler.class.php";
	require_once "AjaxResponse.class.php";
	require_once "Element.class.php";
	require_once "InputElement.class.php";
	require_once "ComboBox.class.php";
	require_once "CheckBox.class.php";
	
	class GetNewElementAjaxRequestHandler implements AjaxRequestHandler
	{
		// Override
		public function processRequest(array $parameters)
		{
			// parse request parameters...when specified, some parameters are expected
			// to be JSON-encoded following a strict scheme (described below)
			
			/*	parameter: type
				text value, specifies the type of the Element (TextField, ComboBox, etc)
			*/
			$type = isset($parameters['type']) ? $parameters['type'] : "";
			
			/*	parameter: name
				text value, specifies the name of the Element (most Elements require a name to be present)
			*/
			$name = isset($parameters['name']) ? $parameters['name'] : "";
			
			/*	parameter: value
				text value, specifies the initial value of the Element
			*/
			$value = isset($parameters['value']) ? $parameters['value'] : "";
			
			/*	parameter: isSelected
				text value, "true" if true, otherwise false
				note:	this is used for CheckBox Elements only
			*/
			$isSelected = (isset($parameters['isSelected']) && $parameters['isSelected'] == "true") ? true : false;
			
			/* parameter: options
				JSON-encoded object, key-value map specifying the label/value pairs (for ComboBox and RadioButtonGroup types)
				example:
					{
						"Text Label 1" : "value1",
						"Text Label 2" : "value2"]
					}
			*/
			$options = isset($parameters['options']) ? json_decode($parameters['options'], true) : array();
			
			/*	parameter: attributes
				JSON-encoded object, key-value map specifying Element attribute/value pairs
				example:
					{
						"someAttribute1"	: "attrValue1",
						"someAttribute2"	: "attrValue2"
					}
			*/
			$attributes = isset($parameters['attributes']) ? json_decode($parameters['attributes'], true) : array();
			
			/*	parameter: classes
				space-delimited text value, specifies list of classes to associate the Element with, overriding any default classes the Element may contain
				example:
					"someClass1 someClass2 someClass3"
			*/
			$classes = isset($parameters['classes']) ? explode(" ", $parameters['classes']) : array();
			
			/*	parameter: addClasses
				space-delimited text value, specifies list of classes to add to the Element's default list of classes (if any)
				example:
					"someClass1 someClass2 someClass3"
			*/
			$addClasses = isset($parameters['addClasses']) ? explode(" ", $parameters['addClasses']) : array();
			
			/*	parameter: eventHandlers
				JSON-encoded object, key-array map specifying a list of events and their associated event handlers
				note:	recognized event names are those defined by JQuery (see http://api.jquery.com/bind/ for a list of recognized events)
						custom event names can also be used
				example:
					{
						"click"			: ["clickHandler1", "clickHandler2"],
						"mouseover "	: ["mouseoverHandler1"]
					}
			*/
			$eventHandlers = isset($parameters['eventHandlers']) ? json_decode($parameters['eventHandlers'], true) : array();
			
			/*	parameter: styles
				JSON-encoded object, key-value map specifying Element styles
				example:
					{
						"display"		: "none",
						"font-weight"	: "bold"
					}
			*/
			$styles = isset($parameters['styles']) ? json_decode($parameters['styles'], true) : array();
			
			if (empty($type))
				return new AjaxResponse("", array("Element type not specified"));
			
			@include_once "$type.class.php";
			
			if (!class_exists($type))
				return new AjaxResponse("", array("The specified Element type does not exist"));
			
			$elementObj = new $type($name);
				
			if (!($elementObj instanceof Element))
				return new AjaxResponse("", array("The specified Element type does not exist"));
				
			if ($elementObj instanceof ComboBox)
				$elementObj->setOptions($options);
				
			if ($elementObj instanceof InputElement)
				$elementObj->setValue($value);
				
			if ($elementObj instanceof CheckBox)
				$elementObj->setSelected($isSelected);
				
			foreach ($attributes as $attrName => $attrValue)
				$elementObj->setAttribute($attrName, $attrValue);
				
			if (!empty($classes))
				$elementObj->setClasses($classes);
				
			if (!empty($addClasses))
				$elementObj->addClasses($addClasses);
			
			foreach ($eventHandlers as $event => $handlers)
			{
				foreach ($handlers as $handler)
					$elementObj->addHandler($event, $handler);
			}
			
			if (!empty($styles))
				$elementObj->setStyles($styles);

			return new AjaxResponse	(
										(object) array	(
															"html" => $elementObj->content(),
															"js" => $elementObj->javascript()
														)
									);
		}
	
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>