<?php
	require_once "TextField.class.php";
	
	class RemoveableTextField extends TextField
	{
		public function __construct($name = "", $value = "")
		{
			parent::__construct($name, $value);
		}
		
		// Override
		public function content()
		{
			$superOut = parent::content();
			$name = $this->getName();			
			
			return <<<END
			
			<div class="removeable" id="{$name}Container">
				$superOut<div style="width: 30px; display: inline-block;">&nbsp;<button id="{$name}RmButton" class="elwoodInput removeBtn" title="Remove">-</button></div>
			</div>
END;
		}
		
		// Override
		public function javascript()
		{
			$container = $this->getName() . "Container";
			$rmButton = $this->getName() . "RmButton";
			
			return parent::javascript() . <<<END
			
			$(function()
			{
				$("#$container").mouseover(function()
				{
					$("#$rmButton").show();
				});
				
				$("#$container").mouseout(function()
				{
					$("#$rmButton").hide();
				});
				
				$("#$rmButton").click(function()
				{
					$("#$container").remove();
				});
			});
END;
		}
	}
?>