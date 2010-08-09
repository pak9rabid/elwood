<?php
	require_once "UDPProtocol.class.php";

	class TCPProtocol extends UDPProtocol
	{
		// Attributes
		protected $flags = array();

		// Constructors
		public function __construct($dport, $sport, array $flags)
		{
			parent::__construct($dport, $sport);
			$this->name = "tcp";
			$this->tcpFlags = $flags;
		}

		// Methods
		public function setFlags(array $flags)
		{
			$this->flags = $flags;
		}

		public function getFlags()
		{
			return $this->flags;
		}
	}
?>
