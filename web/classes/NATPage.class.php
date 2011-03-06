<?php
	require_once "Page.class.php";
	require_once "RouterSettings.class.php";
	require_once "FirewallChain.class.php";
	require_once "NATOutgoingPage.class.php";
	require_once "NATPortForwardingPage.class.php";
	require_once "NATOneToOnePage.class.php";
	require_once "PageSections.class.php";
	
	class NATPage implements Page
	{
		private $tabs = array();
		private $activeTab;
		
		public function __construct(array $parameters = array())
		{			
			$this->tabs = array	(
									"NATOutgoing" => new NATOutgoingPage(),
									"NATPortForwarding" => new NATPortForwardingPage(),
									"NATOneToOne" => new NATOneToOnePage()
								);
								
			$tab = $parameters['tab'];
			
			$this->activeTab = in_array($tab, array_keys($this->tabs)) ? $this->tabs[$tab] : $this->tabs['NATOutgoing'];
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
			return $this->activeTab->head($parameters);
		}
		
		// Override
		public function style(array $parameters)
		{
			return $this->activeTab->style($parameters) . <<<END
			
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
			return $this->activeTab->javascript($parameters);
		}
		
		// Override
		public function content(array $parameters)
		{
			return PageSections::subPages($this, $this->activeTab, $this->tabs, $parameters)
			. <<<END
			
			<button type="button" id="saveBtn">Save</button>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
			return $this->activeTab->popups($parameters);
		}
		
		// Override
		public function isRestricted()
		{
			return true;
		}
	}
?>