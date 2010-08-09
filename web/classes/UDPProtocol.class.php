<?php
	require_once "NetworkProtocol.class.php";

	class UDPProtocol extends NetworkProtocol
	{
		// Attributes
		protected $sport;
		protected $dport;

		// Constructors
		public function __construct($sport, $dport)
		{
			$this->name = "udp";
			$this->sport = $sport;
			$this->dport = $dport;
		}

		// Methods
		public function setSport($sport)
		{
			$this->sport = $sport;
		}

		public function setDport($dport)
		{
			$this->dport = $dport;
		}

		public function getSport()
		{
			return $this->sport;
		}

		public function getDport()
		{
			return $this->dport;
		}

	}
?>
