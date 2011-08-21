<?php
	require_once "Element.class.php";
	require_once "FirewallChain.class.php";
	require_once "Table.class.php";
	require_once "TableRow.class.php";
	require_once "TableCell.class.php";
	require_once "Button.class.php";
	
	class FirewallRulesTable extends Element
	{
		protected $firewallChain;
		protected $table;
		protected $popups = "";
		protected $editButtons = array();
		
		public function __construct($name = "", FirewallChain $chain = null)
		{
			$this->setName($name);
			$this->firewallChain = $chain;
			$this->generateContent();
		}
		
		// Override
		public function content()
		{
			return $this->table->content() . $this->popups;
		}
		
		// Override
		public function javascript()
		{
			$js = parent::javascript() . $this->table->javascript() . <<<END
			
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

			foreach ($this->editButtons as $button)
			{
				$tempJs = $button->javascript();
				
				if (!empty($tempJs))
					$js .= $tempJs;
			}
			
			return $js;
		}
		
		public function getFirewallChain()
		{
			return $this->firewallChain;
		}
		
		public function setFirewallChain(FirewallChain $chain)
		{
			$this->firewallChain = $chain;
			$this->generateContent();
		}
		
		protected function generateContent()
		{
			$this->table = new Table("firewall-table");
			$headingRow = new TableRow("", array	(
														new TableCell("", "Proto", true),
														new TableCell("", "Source", true),
														new TableCell("", "Port", true),
														new TableCell("", "Destination", true),
														new TableCell("", "Port", true),
														new TableCell("", "&nbsp;", true)
													));
													
			$this->table->addRow($headingRow);
			$this->popups = "";
			
			foreach ($this->firewallChain->getRules() as $rule)
			{
				$editButton = new Button($rule->getAttribute("id").editRuleBtn, "Edit");
				$editButton->addHandler("click", "editRule");
				$this->editButtons[] = $editButton;
				
				$row = new TableRow($rule->getAttribute("id"), array	(
																			new TableCell("", $rule->getAttributeDisp("protocol")),
																			new TableCell("", $rule->getAttributeDisp("src_addr")),
																			new TableCell("", $rule->getAttributeDisp("sport")),
																			new TableCell("", $rule->getAttributeDisp("dst_addr")),
																			new TableCell("", $rule->getAttributeDisp("dport")),
																			new TableCell("", $editButton->content())
																		));
				$row->addClass($rule->getAttribute("target") == "ACCEPT" ? "fwRuleAccept" : "fwRuleDrop");
				$row->addHandler("mouseover", "showRuleDetailsPopup");
				$row->addHandler("mouseout", "hideRuleDetailsPopup");
				$this->table->addRow($row);
				
				$this->popups .= <<<END
				<div id="{$rule->getAttribute("id")}details" class="fwRuleDetails">
					<table class=fwDetailsTable">
						<tbody>
							<tr><td class="label">Protocol:</td><td id="{$rule->getAttribute("id")}protocol">{$rule->getAttributeDisp("protocol")}</td></tr>
							<tr><td class="label">Source Address:</td><td id="{$rule->getAttribute("id")}src_addr">{$rule->getAttributeDisp("src_addr")}</td></tr>
							<tr><td class="label">Source Port:</td><td id="{$rule->getAttribute("id")}sport">{$rule->getAttributeDisp("sport")}</td></tr>
							<tr><td class="label">Destination Address:</td><td id="{$rule->getAttribute("id")}dst_addr">{$rule->getAttributeDisp("dst_addr")}</td></tr>
							<tr><td class="label">Destination Port:</td><td id="{$rule->getAttribute("id")}dport">{$rule->getAttributeDisp("dport")}</td></tr>
							<tr><td class="label">States:</td><td id="{$rule->getAttribute("id")}state">{$rule->getAttributeDisp("state")}</td></tr>
							<tr><td class="label">Fragmented:</td><td id="{$rule->getAttribute("id")}fragmented">{$rule->getAttributeDisp("fragmented")}</td></tr>
END;

				if ($rule->getAttributeDisp("protocol") == "icmp")
				{
					$this->popups .= <<<END
							<tr><td class="label">ICMP Type:</td><td id="{$rule->getAttribute("id")}icmp_type">{$rule->getAttributeDisp("icmp_type")}</td></tr>
END;
				}
				
				$this->popups .= <<<END
							<tr><td class="label">Target:</td><td id="{$rule->getAttribute("id")}target">{$rule->getAttributeDisp("target")}</td></tr>
						</tbody>
					</table>
				</div>
END;
			}
		}
	}
?>