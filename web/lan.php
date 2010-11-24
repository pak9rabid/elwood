<?php
   ################################################
   # File :       lan.php                         #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        10-17-2005                      #
   #                                              #
   # Description: Display LAN settings and provide#
   #              an interface to change current  #
   #              LAN settings.                   #
   ################################################

   # Include lan.inc to provide us with the lanConfig class
  // require_once "formatting.inc";
  // require_once "lan.inc";
  
   require_once "accessControl.php";
   require_once "PageElements.class.php";
   require_once "User.class.php";
   require_once "NetworkInterface.class.php";

   # Create LAN config object to get current LAN settings
   //$lanConfig = new lanConfig;
   $lanInt = NetworkInterface::getInstance("lan");

   # Determine which group the current user belongs to
   //$currentUser = getUser();
   //$userGroup   = getGroup($currentUser);
   $currentUser = User::getUser();
   $userGroup = $currentUser->getGroup();
   
   // Testing
   print_r($lanInt);
   // End Testing
?>

<html>
   <head>
      <title>LAN Setup</title>
      <link rel="StyleSheet" type="text/css" href="css/routerstyle.css">
      <script language="JavaScript" src="js/lan.js" type="text/javascript"></script>
   </head>

   <?php
      # Check to see if dhcp server is enabled
      //if ($lanConfig->dhcpServerEnabled)
      if ($lanInt->usesDhcp())
         $isDhcpServerEnabled = "true";
      else
         $isDhcpServerEnabled = "false";
   ?>

   <body onLoad="javascript:setInputsForUser(<? echo "'$userGroup'";?>,<? echo $isDhcpServerEnabled; ?>)">
      <div id="container">
         <?=PageElements::title("LAN Setup")?>
         <?=PageElements::navigation()?>
         <div id="content">
            <?php
               # Check to see if dhcp server is enabled and set radio buttons accordingly
               //if ($lanConfig->dhcpServerEnabled)
               if ($lanInt->usesDhcp())
               {
                  $dhcpSelect1 = "<input type='radio' name='dhcpserver' value='enabled' checked onClick='javascript:enableInputs()'>";
                  $dhcpSelect2 = "<input type='radio' name='dhcpserver' value='disabled' onClick='javascript:disableInputs()'>";
               }
               else
               {
                  $dhcpSelect1 = "<input type='radio' name='dhcpserver' value='enabled' onClick='javascript:enableInputs()'>";
                  $dhcpSelect2 = "<input type='radio' name='dhcpserver' value='disabled' checked onClick='javascript:disableInputs()'>";
               }

               # Print current LAN settings in the form
echo <<<END
               <form name="lanconfig" action="scripts/admin/lanconfig.php" method="POST">
                  <table class="ip-table" width="300px">
                     <tr><th colspan="2">LAN IP Address</th></tr>
                     <tr><td align="right">IP Address:</td><td><input class="textfield" size="20" maxlength="15" name="ipaddress" value="$lanInt->getIp()"></td></tr>
                     <tr><td align="right">Subnet Mask:</td><td><input class="textfield" size="20" maxlength="15" name="netmask" value="$lanInt->getNetmask()"></td></tr>
                  </table>
END;

                  # Print errors, if any
                  if ($_GET['errip'] == true) 
                     echo "<font class='error-font'>Error: Invalid or no IP address entered</font><br>";
                  if ($_GET['errnetmask'] == true)
                     echo "<font class='error-font'><b>Error: Invalid or no subnet mask entered</b></font><br>";

                  # Print dhcp server information in form
echo <<<END
                  <br>
                  <table class="ip-table" width="300px">
                     <tr><th colspan="2">LAN DHCP Server</th></tr>
                     <tr><td align="right">DHCP Server:</td><td>$dhcpSelect1 Enabled</td></tr>
                     <tr><td>&nbsp</td><td>$dhcpSelect2 Disabled</td></tr>
                     <tr><td colspan="2">&nbsp</td></tr>
                     <tr><td align="right">Starting Address:</td><td><input class="textfield" size="20" maxlength="15" name="startip" value=""></td></tr>
                     <tr><td align="right">Ending IP Address:</td><td><input class="textfield" size="20" maxlength="15" name="endip" value=""></td></tr>
                     <tr><td align="right">Nameserver 1:</td><td><input class="textfield" size="20" maxlength="15" name="dns1" value=""></td></tr>
                     <tr><td align="right">Nameserver 2:</td><td><input class="textfield" size="20" maxlength="15" name="dns2" value=""></td></tr>
                     <tr><td align="right">Nameserver 3:</td><td><input class="textfield" size="20" maxlength="15" name="dns3" value=""></td></tr>
                     <tr><th colspan="2">Static DHCP Addresses</th></tr>
                     <tr><th colspan="2">Add New Host</th></tr>
                     <tr><td align="right">Hostname:</td><td><input class="textfield" size="20" maxlength="32" name="newdhcphost"></td></tr>
                     <tr><td align="right">MAC:</td><td><input class="textfield" size="20" maxlength="17" name="newdhcpmac"></td></tr>
                     <tr><td align="right">IP:</td><td><input class="textfield" size="20" maxlength="15" name="newdhcpip"></td></tr>
                  </table>
END;

                  if ($_GET['errstartip'] == true)
                     echo "<font class='error-font'><b>Error: Invalid or no DHCP starting address</b></font><br>";
                  if ($_GET['errendip'] == true)
                     echo "<font class='error-font'><b>Error: Invalid or no DHCP ending address</b></font><br>";
                  if ($_GET['errdns1'] == true)
                     echo "<font class='error-font'><b>Error: Nameserver 1 is invalid</b></font><br>";
                  if ($_GET['errdns2'] == true)
                     echo "<font class='error-font'><b>Error: Nameserver 2 is invalid</b></font><br>";
                  if ($_GET['errdns3'] == true)
                     echo "<font class='error-font'><b>Error: Nameserver 3 is invalid</b></font><br>";
echo <<<END
                  <br>
                  <input type="submit" value="Change">&nbsp<input type="reset">
               </form>
END;
            ?>
         </div>
      </div>
   </body>
</html>
