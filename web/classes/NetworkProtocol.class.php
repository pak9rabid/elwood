<?php
	class NetworkProtocol
	{
		// Attributes
		protected $name;

		// Constructors
		public function __construct($name)
		{
			$this->name = $name;
		}

		// Methods
		public function getName()
		{
			return $this->name;
		}
	}
?>
