<?php    
   ################################################
   # File :       wan.php                         #
   #                                              #
   # Author:      Patrick Griffin                 #
   #                                              #
   # Date:        10-17-2005                      #
   #                                              #
   # Description: Display the current WAN settings#
   #              on the router and provide an    #
   #              interface to change WAN settings#
   ################################################

   # Include required files
   require_once "formatting.inc";
   require_once "wan.inc";

   # Create wan config object to get current wan settings
   $wanConfig = new wanConfig;

   # Determine which group the current user belongs to 
   $currentUser = getUser();
   $userGroup   = getGroup($currentUser);
?>

<html>
   <head>
      <title>WAN Setup</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
      <script language="JavaScript" src="inc/wan.js" type="text/javascript"></script>
   </head>

   <body onLoad="javascript:setInputsForUser(<? echo "'$userGroup'"; ?>,<? echo $wanConfig->useDhcp; ?>)">
      <div id="container">
         <div id="title">
            <?php echo printTitle("WAN Setup"); ?>
         </div>
         <?php
            printNavigation();
         ?>
         <div id="content">
            <?php
               # Determine what radio buttons should be checked by default
               if ($wanConfig->useDhcp)
               {
                  $dynRadio  = "<input type='radio' name='iptype' value='dynip' checked onClick='javascript:disableInputs()'>";
                  $statRadio = "<input type='radio' name='iptype' value='statip' onClick='javascript:enableInputs()'>";
               }
               else
               {
                  $dynRadio  = "<input type='radio' name='iptype' value='dynip' onClick='javascript:disableInputs()'>";
                  $statRadio = "<input type='radio' name='iptype' value='statip' checked onClick='javascript:enableInputs()'>";
               }
echo <<<END
               <form name="wanconfig" action="scripts/admin/wanconfig.php" method="POST">
                  <br>
                  <table id="ip-table" width="300px">
                     <tr><th colspan="3">IP Address</th></tr>
                     <tr cellspacing="0">
                        <td>$dynRadio</td><td colspan="2">Obtain IP adress automatically</td>
                     </tr>
                     <tr cellspacing="0">
                        <td>$statRadio</td><td colspan="2">Specify IP address:</td>
                     </tr>
                     <tr><td>&nbsp</td>
                        <td colspan="2"><hr></td>
                     </tr>
                     <tr>
                        <td>&nbsp</td>
                        <td align="right">IP Address:</td><td><input id="textfield" size="20" maxlength="15" name="ipaddress" value="$wanConfig->ipAddress"></td>
                     </tr>
                     <tr><td>&nbsp</td>
                     <td align="right">Subnet Mask:</td><td><input id="textfield" size="20" maxlength="15" name="netmask" value="$wanConfig->netmask"></td>
                     </tr>
                     <tr>
                        <td>&nbsp</td>
                        <td align="right">Gateway:</td><td><input id="textfield" size="20" maxlength="15" name="gateway" value="$wanConfig->gateway"></td>
                     </tr>
                  </table>
END;
                  # Print out errors if any
                  if ($_GET['errip'] == true)
                     echo "<font id='error-font'><b>Error: Invalid or no IP address entered</b></font><br>";
                  if ($_GET['errnetmask'] == true)
                     echo "<font id='error-font'><b>Error: Invalid or no subnet mask entered</b></font><br>";
                  if ($_GET['errgw'] == true)
                     echo "<font id='error-font'><b>Error: Invalid gateway entered</b></font><br>";
                  echo "<br>";

echo <<<END
                  <table id="ip-table" width="300px">
                     <tr><th colspan="2">DNS</th></tr>
                     <tr>
                        <td align="right">Nameserver 1:</td>
                        <td><input id="textfield" size="20" maxlength="15" name="dns1" value="$wanConfig->dns1"></td>
                     </tr>
                     <tr>
                        <td align="right">Nameserver 2:</td>
                        <td><input id="textfield" size="20" maxlength="15" name="dns2" value="$wanConfig->dns2"></td>
                     </tr>
                     <tr>
                        <td align="right">Nameserver 3:</td>
                        <td><input id="textfield" size="20" maxlength="15" name="dns3" value="$wanConfig->dns3"></td>
                     </tr>
                  </table>
END;
                  
                  # Print out errors if any
                  if ($_GET['errdns1'] == true)
                     echo "<font id='error-font'><b>Error: Nameserver 1 is invalid</b></font><br>";
                  if ($_GET['errdns2'] == true)
                     echo "<font id='error-font'><b>Error: Nameserver 2 is invalid</b></font><br>";
                  if ($_GET['errdns3'] == true)
                     echo "<font id='error-font'><b>Error: Nameserver 3 is invalid</b></font></br>";
                  echo "<br>";
echo <<<END
                  <table id="access-table" width="300px">
                     <tr><th>MTU Size</th></tr>
                     <tr><td><input id="textfield" size="4" maxlength="10" name="mtusize" value="$wanConfig->mtuSize"></td></tr>
                  </table>
                  <br>
                  <input type="submit" value="Change">&nbsp<input type="reset">
               </form>
END;
            ?>
         </div>
      </div>
   </body>
</html>
