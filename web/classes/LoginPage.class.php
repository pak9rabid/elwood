<?php
	require_once "Page.class.php";
	require_once "TextField.class.php";
	require_once "Button.class.php";
	require_once "SessionUtils.class.php";
	
	class LoginPage extends Page
	{
		public function __construct()
		{
			SessionUtils::logout();
			
			$this->addElement(new TextField("username"));
			$this->addElement(new TextField("password"));
			$this->addElement(new Button("loginBtn", "Login"));
			
			$this->getElement("username")->setAttribute("size", "15");
			$this->getElement("username")->setAttribute("maxlength", "32");
			
			$this->getElement("password")->setAttribute("size", "15");
			$this->getElement("password")->setAttribute("maxlength", "32");
			$this->getElement("password")->setAttribute("type", "password");
			
			$this->getElement("loginBtn")->setAttribute("type", "submit");
		}
		
		// Override
		public function id()
		{
			return "Login";
		}
		
		// Override
		public function name()
		{
			return "Login Page";
		}
		
		// Override
		public function head(array $parameters)
		{
		}
		
		// Override
		public function style(array $parameters)
		{
			return <<<END
			
			table
			{
				margin-left: auto;
				margin-right: auto;
			}
END;
		}
		
		// Override
		public function javascript(array $parameters)
		{
			return parent::javascript($parameters) . <<<END
			
			function submitLogin()
			{
				var params =	{
									handler: "Login",
									parameters:
									{
										username: $("#username").val(),
										password: $("#password").val()
									}
								};
								
				$.getJSON("ajax/ajaxRequest.php", params, function(response)
				{
					$("#username").val("");
					$("#password").val("");
					
					if (response.errors.length > 0)
					{
						$("#loginMessages")
							.css("color", "red")
							.html("<ul><li>" + response.errors.join("</li><li>") + "</li></ul>")
							.show();
					}
					else
					{
						$("#loginMessages")
							.css("color", "green")
							.html("Login successful")
							.show()
							.fadeOut(3000);
					}
				});
			}
END;
		}
		
		// Override
		public function content(array $parameters)
		{
			return <<<END
			
			<b>Login required:</b>
			<br><br>
			<form action="javascript:submitLogin()">
			<table>
				<tr><th>Username:</th><td>{$this->getElement("username")}</td></tr>
				<tr><th>Password:</th><td>{$this->getElement("password")}</td></tr>
			</table>
			<div id="loginMessages"></div>
			<br>
			{$this->getElement("loginBtn")}
			</form>
END;
		}
		
		// Override
		public function popups(array $parameters)
		{
		}
		
		// Override
		public function isRestricted()
		{
			return false;
		}
	}
?>