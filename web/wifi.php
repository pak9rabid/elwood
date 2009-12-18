<?
   # Include required files
   require_once "wifi.inc";

   # Get current wifi settings
   $wifiConfig = new wifiConfig;

   # Check to see which wifi options are enabled
   # and set vars to select radio buttons by default
   $wifiEnabledSelect       = "";
   $wifiDisabledSelect      = "";
   $ssidBcastEnabledSelect  = "";
   $ssidBcastDisabledSelect = "";
   $wepEnabledSelect        = "";
   $wepDisabledSelect       = "";
   $wepRestrictedSelect     = "";
   $wepOpenSelect           = "";

   if ($wifiConfig->isWifiEnabled)
      $wifiEnabledSelect = "checked";
   else
      $wifiDisabledSelect = "checked";

   if ($wifiConfig->isSsidBcastEnabled)
      $ssidBcastEnabledSelect = "checked";
   else
      $ssidBcastDisabledSelect = "checked";

   if ($wifiConfig->isWepEnabled)
      $wepEnabledSelect = "checked";
   else
      $wepDisabledSelect = "checked";

   if ($wifiConfig->wepMode == "restricted")
      $wepRestrictedSelect = "checked";
   else
      $wepOpenSelect = "checked";
?>

<html>
   <head>
      <title>WiFi Setup</title>
      <link rel="StyleSheet" type="text/css" href="routerstyle.css">
   </head>

   <body>
      <div id="container">
         <div id="title">
            <?php 
               require "formatting.inc";
               echo printTitle("WiFi Setup");
            ?>
         </div>
         <?php 
            printNavigation();
         ?>
         <div id="content">
            <form action="../scripts/admin/wificonfig.php" method="POST">
               <table id="ip-table" width="350px">
                  <tr>
                     <th colspan="2">Basic Settings</th>
                  </tr>
                  <tr>
                     <td align="right">WiFi:</td>
                     <td align="left"><input type="radio" name="wifiEnabled" value="true" <? echo $wifiEnabledSelect; ?>>Enabled</td>
                  </tr>
                  <tr>
                     <td>&nbsp</td>
                     <td align="left"><input type="radio" name="wifiEnabled" value="false" <? echo $wifiDisabledSelect; ?>>Disabled</td>
                  </tr>
                  <tr>
                     <td colspan="2">&nbsp</td>
                  </tr>
                  <tr>
                     <td align="right">SSID Broadcast:</td>
                     <td align="left"><input type="radio" name="ssidEnabled" value="true" <? echo $ssidBcastEnabledSelect; ?>>Enabled</td>
                  </tr>
                  <tr>
                     <td>&nbsp</td>
                     <td align="left"><input type="radio" name="ssidEnabled" value="false" <? echo $ssidBcastDisabledSelect; ?>>Disabled</td>
                  </tr>
                  <tr>
                     <td colspan="2">&nbsp</td>
                  </tr>
                  <tr>
                     <td align="right">SSID:</td>
                     <td align="left"><input name="ssid" size="16" maxlength="32" value="<? echo $wifiConfig->ssid; ?>"></td>
                  </tr>
               </table>
               <br>
               <table id="ip-table" width="350px">
                  <tr>
                     <th colspan="2">Security Settings</th>
                  </tr>
                  <tr>
                     <td align="right">WEP Encryption:</td>
                     <td align="left"><input type="radio" name="wepEnabled" value="true" <? echo $wepEnabledSelect; ?>>Enabled</td>
                  </tr>
                  <tr>
                     <td>&nbsp</td>
                     <td align="left"><input type="radio" name="wepEnabled" value="false" <? echo $wepDisabledSelect; ?>>Disabled</td>
                  </tr>
                  <tr>
                     <td colspan="2">&nbsp</td>
                  </tr>
                  <tr>
                     <td align="right">WEP Key:</td>
                     <td align="left"><input name="wepKey" size="32" maxlength="32" value="<? echo $wifiConfig->wepKey; ?>"></td>
                  </tr>
                  <tr>
                     <td colspan="2">&nbsp</td>
                  </tr>
                  <tr>
                     <td align="right">WEP Mode:</td>
                     <td align="left"><input type="radio" name="wepMode" value="restricted" <? echo $wepRestrictedSelect; ?>>Restricted</td>
                  </tr>
                  <tr>
                     <td>&nbsp</td>
                     <td align="left"><input type="radio" name="wepMode" value="open" <? echo $wepOpenSelect; ?>>Open</td>
                  </tr>
               </table>
               <br>
               <input type="submit" value="Change">&nbsp<input type="reset">
            </form>
         </div>
      </div>
   </body>
</html>
