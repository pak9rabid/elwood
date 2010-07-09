<?php
   ###############################################
   # File :       wol.php                        #
   #                                             #
   # Author:      Patrick Griffin                #
   #                                             #
   # Date:        11-25-2005                     #
   #                                             #
   # Description: Provides an interface to wake  #
   #              up computers on the LAN via    #
   #              wake on lan                    #
   ###############################################

   # Include required files
   require "formatting.inc";

   # Check for any returned errors
   $error = $_GET['error'];
?>

<html>
   <head>
      <title>Wake On LAN</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
      <script lang="JavaScript" type="text/javascript" src="inc/wol.js"></script>
   </head>

   <body onLoad="hideOptions()">
      <div id="container">
         <div id="title">
            <?php
               printTitle("Wake On LAN");
            ?>
         </div>
         <div id="navigation">
            <?php
               printNavigation();
            ?>
         </div>
         <div id="content">
            <form name="wol" action="scripts/process_wol.php" method="POST">
               Enter the MAC address of the computer to wake up:
               <br><br>
               <table class="status-table">
                  <tr><th>MAC Address:</th><td align="left"><input name="mac_addr" size="20" maxlength="17"></td></tr>
               </table>
               <?php
                  # Get errors from script, if any
                  $success   = $_GET['success'];
                  $errorMac  = $_GET['errmac'];
                  $errorPass = $_GET['errpass'];
                  $errorPort = $_GET['errport'];
                  
                  # If an error was returned, display here
                  if ($errorMac)
                  {
                     $prevMac = $_GET['mac'];
                     echo "<font class='error-font'>Error: $prevMac is not a valid MAC address</font>";
                  }

                  if ($errorPass)
                     echo "<font class='error-font'>Error: Password entered is not in the format of a valid MAC address</font>";

                  if ($errorPort)
                     echo "<font class='error-font'>Error: Port number entered is not valid</font>";

                  if ($success)
                  {
                     $prevMac = $_GET['mac'];
                     echo "<font class='success-font'>Waking up $prevMac</font>";
                  }
               ?>
               <br>
               <input type="checkbox" name="show_options" value="true" onClick="javascript:toggleOptions()">Additional options
               <div id="removable">
                  <table class="status-table">
                     <tr><th>Password:</th><td><input type="password" name="password" size="20" maxlength="255"></td></tr>
                     <tr><th>Port:</th><td><input name="port" size="20" maxlength="5"></td></tr>
                     <tr><th>Host:</th><td><input name="host" size="20" maxlength="255"></td></tr>
                  </table>
               </div>
               <br>
               <input type="submit" value="Wake Up">
            </form>
         </div>
      </div>
   </body>
</html>
