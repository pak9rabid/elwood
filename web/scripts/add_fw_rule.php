<?php
   # Include required files
   require_once "RouterSettings.class.php";
?>

<html>
   <head>
      <title>Add Firewall Rule</title>
      <link rel="StyleSheet" type="text/css" href="../fwdetail.css">
   </head>

   <body>
      <div id="content">
         <?php
            # Path to files
            $protocolsFile = RouterSettings::getSettingValue("PROTOCOLS");

            # Commonly used commands
            $SHOWINPUT          = "sudo ../bin/show_input_table";
            $SHOWOUTPUT         = "sudo ../bin/show_output_table";
            $getProtocolListCmd = "cat $protocolsFile | cut -f1 | egrep -v [#]";

            # Pull data from form
            $stream = $_GET['stream'];

            # Get a list of valid iptables protocols from /etc/protocols
            $protocolList = array();
            unset($protocolList);
            exec($getProtocolListCmd, $protocolList);

echo <<<END
            <b>Add Firewall Rule</b>
            <form action="admin/change_firewall.php" method="GET">
               <input type="hidden" name="action" value="add">
               <input type="hidden" name="stream" value="$stream">
               <table class="access-table">
                  <tr><th>Stream</th><th>Port</th><th>Protocol</th><th>Source</th><th>Destination</th><th>Job</th></tr>
                  <tr><td>$stream</td><td><input name='port' size='5' maxlength='5'></td><td>
                  <select name="protocol">
                     <option value="all">
END;
                     foreach ($protocolList as $value)
                        echo "<option value='$value'>$value";
                  echo "</select>";
echo <<<END
                  </td><td><input name='sourceip' size='10' maxlength='18'></td><td><input name="destip" size="10" maxlength="18"></td><td>
                <select name="job">
                   <option value="DROP">DROP
                   <option value="ACCEPT">ACCEPT
                </select></td></tr>
                  <tr><th colspan="6">TCP States</th></tr>
                  <tr><td colspan="6">
                     <div id="states">
                        <ul>
                           <li><input type="checkbox" name="state_est" value="ESTABLISHED">ESTABLISHED</li>
                           <li><input type="checkbox" name="state_rel" value="RELATED">RELATED</li>
                           <li><input type="checkbox" name="state_new" value="NEW">NEW</li>
                           <li><input type="checkbox" name="state_inv" value="INVALID">INVALID</li>
                        </ul>
                     </div>
                  </td></tr>
               </table>
               <br>
               <input type="submit" value="Add Rule">
            </form>
END;
         ?>
      </div>
   </body>
</html>
