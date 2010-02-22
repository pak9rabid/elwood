<?php
   ##########################################
   # File:          formatting.inc          #
   #                                        #
   # Author:        Patrick Griffin         #
   #                                        #
   # Last modified: 10-16-2005              #
   #                                        #
   # Description:   Provides functions to   #
   #                format each router page #
   ##########################################
   
   require_once "User.class.php";

   function printNavigation()
   # Preconditions:  Function is called from within the navigation div tag
   # Postconditions: Prints the navigational links for the site
   {
echo <<<END
      <div id="navigation">
         <a href="status.php">Status</a>
         <a href="access.php">Access</a>
         <a href="wan.php">WAN</a>
         <a href="lan.php">LAN</a>
         <a href="wifi.php">WiFi</a>
         <a href="wol.php">WOL</a>
         <a href="firewall.php">Firewall</a>
         <a href="portforward.php">Port Forwarding</a>
         <a href="webterm.php">WebTerm</a>
      </div>
END;
   }

   function printTitle($title)
   # Preconditions:  Function is called from within the title div tag
   # Postconditions: Prints the page title for the site
   {
      $user = User::getUser();
      
      echo "<font id='usertxt'>";
      
      if (!empty($user))
         echo "Logged in: " . $user->getAttribute("username") . "<br />" .
              "<a href='inc/accessControl.php?logout=true'>Logout</a>";
      else
         echo "&nbsp;";
         
      echo "</font>";
      echo "$title";
   }
?>
