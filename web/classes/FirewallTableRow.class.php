<?php
	require_once "TableRow.class.php";
	require_once "FirewallRule.class.php";
	require_once "Button.class.php";
	require_once "TableCell.class.php";
	
	class FirewallTableRow extends TableRow
	{
		protected $editButton;
		
		public function __construct(FirewallRule $rule)
		{
			if ($rule->getAttribute("id") == null)
				throw new Exception("FirewallRule must have an id associated with it");
				
			$this->name = $rule->getAttribute("id");
			$this->editButton = new Button($rule->getAttribute("id") . "EditBtn", "Edit");
			
			$this->editButton->addHandler("click", "editRule");
			
			$this->generateRowContent($rule);
			$this->addHandler("mouseover", "showRuleDetailsPopup");
			$this->addHandler("mouseout", "hideRuleDetailsPopup");
		}
		
		public function getEditButton()
		{
			return $this->editButton;
		}
		
		public function setFirewallRule(FirewallRule $rule)
		{
			$this->generateRowContent($rule);
			return $this;
		}
		
		// Override
		public function javascript()
		{
			return parent::javascript() . $this->editButton->javascript();
		}
		
		protected function generateRowContent(FirewallRule $rule)
		{
			$this->clearCells();
			$this->setClasses(array());
			
			$this->addClass($rule->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop");
			$this->addCell(new TableCell("", $rule->getAttributeDisp("protocol")));
			$this->addCell(new TableCell("", $rule->getAttributeDisp("src_addr")));
			$this->addCell(new TableCell("", $rule->getAttributeDisp("sport")));
			$this->addCell(new TableCell("", $rule->getAttributeDisp("dst_addr")));
			$this->addCell(new TableCell("", $rule->getAttributeDisp("dport")));
			$this->addCell(new TableCell("", $this->editButton->content()));
		}
	}
?>