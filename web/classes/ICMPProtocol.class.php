<?php
	require_once "NetworkProtocol.class.php";

	class ICMPProtocol extends NetworkProtocol
	{
		// Attributes
		protected $type;

		// Constructors
		public function __construct($type)
		{
			$this->name = "icmp";
			$this->type = $type;
		}

		// Methods
		public function getType()
		{
			return $this->type;
		}
	}
?>
