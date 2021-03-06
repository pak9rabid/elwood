<?php
	require_once "Element.class.php";
	require_once "FirewallChain.class.php";
	require_once "Table.class.php";
	require_once "FirewallTableRow.class.php";
	require_once "TableRow.class.php";
	require_once "TableCell.class.php";
	
	class FirewallRulesTable extends Element
	{
		protected $firewallChain;
		protected $table;
		protected $popups = array();
		
		public function __construct($name = "", FirewallChain $chain)
		{
			$this->setName($name);
			$this->firewallChain = $chain;
			$this->updateContent();
		}
		
		public static function firewallRuleToTableRow(FirewallRule $rule)
		{
			return new FirewallTableRow($rule);
		}
				
		public static function firewallRuleToTable(FirewallRule $rule)
		{
			$ruleId = $rule->getAttribute("id");
			$ruleDetailsTable = new Table	($ruleId . "detailsTable", array	(
																					new TableRow	("", array	(
																													TableCell::newCell("", "Protocol:")->addClass("label"),
																													TableCell::newCell($ruleId . "protocol", $rule->getAttributeDisp("protocol"))
																												)
																									),
																					
																					new TableRow	("", array	(
																													TableCell::newCell("", "Source Address:")->addClass("label"),
																													TableCell::newCell($ruleId . "src_addr", $rule->getAttributeDisp("src_addr")),
																												)
																									),
																					new TableRow	("", array	(
																													TableCell::newCell("", "Source Port:")->addClass("label"),
																													TableCell::newCell($ruleId . "sport", $rule->getAttributeDisp("sport"))
																												)
																									),
																					new TableRow	("", array	(
																													TableCell::newCell("", "Destination Address:")->addClass("label"),
																													TableCell::newCell($ruleId . "dst_addr", $rule->getAttributeDisp("dst_addr"))
																												)
																									),
																					new TableRow	("", array	(
																													TableCell::newCell("", "Destination Port:")->addClass("label"),
																													TableCell::newCell($ruleId . "dport", $rule->getAttributeDisp("dport"))
																												)
																									),
																					new TableRow	("", array	(
																													TableCell::newCell("", "States:")->addClass("label"),
																													TableCell::newCell($ruleId . "state", $rule->getAttributeDisp("state"))
																												)
																									),
																					new TableRow	("", array	(
																													TableCell::newCell("", "Fragmented:")->addClass("label"),
																													TableCell::newCell($ruleId . "fragmented", $rule->getAttributeDisp("fragmented"))
																												)
																									)
																				)
											);
											
			$ruleDetailsTable->addClass("fwDetailsTable");
											
			if ($rule->getAttribute("protocol") == "icmp")
				$ruleDetailsTable->addRow(new TableRow("", array(TableCell::newCell("", "ICMP Type:")->addClass("label"), TableCell::newCell($ruleId . "icmp_type", $rule->getAttributeDisp("icmp_type")))));
				
			$ruleDetailsTable->addRow(new TableRow("", array(TableCell::newCell("", "Target:")->addClass("label"), TableCell::newCell($ruleId . "target", $rule->getAttributeDisp("target")))));
			
			return $ruleDetailsTable;
		}
		
		public function getTable()
		{
			return $this->table;
		}
		
		public function getPopups()
		{
			return $this->popups;
		}
		
		// Override
		public function content()
		{
			return $this->table->content() . implode("\n", $this->popups);
		}
		
		// Override
		public function javascript()
		{
			return parent::javascript() . $this->table->javascript() . <<<END
			
			function showRuleDetailsPopup(e)
			{
				$("#" + $(this).attr("id") + "details")
					.css	(
								{
									position: "absolute",
									left: e.pageX,
									top: $(this).position().top + $(this).height(),
									display: "inline"
								}
							)
					.show();
			}
			
			function hideRuleDetailsPopup(e)
			{
				$("#" + $(this).attr("id") + "details").hide();
			}
END;
		}
		
		public function getFirewallChain()
		{
			return $this->firewallChain;
		}
		
		public function setFirewallChain(FirewallChain $chain)
		{
			$this->firewallChain = $chain;
			$this->updateContent();
		}
		
		public function updateContent()
		{
			$this->table = new Table("firewall-table");
			$this->popups = array();
			
			$headingRow = new TableRow("", array	(
														new TableCell("", "Proto", true),
														new TableCell("", "Source", true),
														new TableCell("", "Port", true),
														new TableCell("", "Destination", true),
														new TableCell("", "Port", true),
														new TableCell("", "&nbsp;", true)
													));
													
			$this->table->addRow($headingRow->addClasses(array("nodrag", "nodrop")));
			
			foreach ($this->firewallChain->getRules() as $rule)
			{
				$this->table->addRow(self::firewallRuleToTableRow($rule));
				$this->popups[] =	"<div id=\"" . $rule->getAttribute("id") . "details\" class=\"fwRuleDetails\">" .
										self::firewallRuleToTable($rule)->content() .
									"</div>";
			}
		}
	}
?>