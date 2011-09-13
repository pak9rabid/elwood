<?php
	require_once "TableRow.class.php";
	require_once "FirewallRule.class.php";
	require_once "Button.class.php";
	require_once "TableCell.class.php";
	
	class FirewallTableRow extends TableRow
	{
		protected $editButton;
		protected $rule;
		
		public function __construct(FirewallRule $rule)
		{
			$this->rule = $rule;
			
			if ($rule->getAttribute("id") == null)
				throw new Exception("FirewallRule must have an id associated with it");
				
			$this->name = $rule->getAttribute("id");
			$this->editButton = new Button($rule->getAttribute("id") . "EditBtn", "Edit");
			
			$this->editButton->addHandler("click", "editRule");
			
			$this->updateContent();
			$this->addHandler("mouseover", "showRuleDetailsPopup");
			$this->addHandler("mouseout", "hideRuleDetailsPopup");
		}
		
		public function getEditButton()
		{
			return $this->editButton;
		}
		
		public function setFirewallRule(FirewallRule $rule)
		{
			$this->rule = $rule;
			$this->updateContent();
			return $this;
		}
		
		// Override
		public function javascript()
		{				
			return parent::javascript() . $this->editButton->javascript();
		}
				
		public function updateContent()
		{
			$this->clearCells();
			$this->setClasses(array());
			
			$this->addClass($this->rule->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop");
			$this->addCell(new TableCell("", $this->rule->getAttributeDisp("protocol")));
			$this->addCell(new TableCell("", $this->rule->getAttributeDisp("src_addr")));
			$this->addCell(new TableCell("", $this->rule->getAttributeDisp("sport")));
			$this->addCell(new TableCell("", $this->rule->getAttributeDisp("dst_addr")));
			$this->addCell(new TableCell("", $this->rule->getAttributeDisp("dport")));
			$this->addCell(new TableCell("", $this->editButton->content()));
		}
	}
?>