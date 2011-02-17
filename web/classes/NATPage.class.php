<?php
	require_once "Page.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallChain.class.php";
	require_once "OutgoingNatPage.class.php";
	require_once "PortForwardNatPage.class.php";
	require_once "OneToOneNatPage.class.php";
	require_once "PageSections.class.php";
	
	class NatPage implements Page
	{
		private $natOutPage;
		private $natPforwardPage;
		private $natOneToOnePage;
		private $activePage;
		
		public function __construct(array $parameters = array())
		{
			$this->natOutPage = new OutgoingNatPage();
			$this->natPforwardPage = new PortForwardNatPage();
			$this->natOneToOnePage = new OneToOneNatPage();
			
			$tab = in_array($parameters['tab'], array("nat-pforward", "nat-121")) ? $parameters['tab'] : "nat-out";
			
			switch ($tab)
			{
				case "nat-out":
					$this->activePage = $this->natOutPage;
					break;
				case "nat-pforward":
					$this->activePage = $this->natPforwardPage;
					break;
				case "nat-121":
					$this->activePage = $this->natOneToOnePage;
					break;
			}
		}
		
		// Override
		public function id()
		{
			return "NAT";
		}
		
		// Override
		public function name()
		{
			return "NAT";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
			return <<<END
			
			.nat-table
			{
				text-align: center;
				border-collapse: collapse;
				margin-left: auto;
				margin-right: auto;
			}
			
			.nat-table tr
			{
				border: 1px solid;
			}
			
			.nat-table th
			{
				background-color: #A8A8A8;
			}
END;
		}
		
		// Override
		public function javascript(array $parameters)
		{
			return $this->activePage->javascript($parameters);
		}
		
		// Override
		public function content(array $parameters)
		{			
			return PageSections::subPages($this, $this->activePage, array($this->natOutPage, $this->natPforwardPage, $this->natOneToOnePage), $parameters)
			. <<<END
			
			<button type="button" id="saveBtn">Save</button>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			return $this->activePage->popups($parameters);
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>