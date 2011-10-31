<?php
	require_once "TextField.class.php";
	require_once "Button.class.php";
	
	class RemoveableTextField extends TextField
	{
		protected $rmButton;
		
		public function __construct($name = "", $value = "")
		{
			parent::__construct($name, $value);
			$this->rmButton = new Button($name . "RmButton", "-");
			$this->rmButton->addClass("removeBtn");
		}
		
		// Override
		public function content()
		{
			$superOut = parent::content();
			$name = $this->getName();			
			
			return <<<END
			
			<div class="removeable" id="{$name}Container">
				$superOut<div style="width: 30px; display: inline-block;">&nbsp;{$this->rmButton}</div>
			</div>
END;
		}
		
		// Override
		public function javascript()
		{
			$container = $this->getName() . "Container";
			$rmButton = $this->getName() . "RmButton";
			
			return parent::javascript() . $this->rmButton->javascript() . <<<END
			
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
		
		public function getRmButton()
		{
			return $this->rmButton;
		}
	}
?>