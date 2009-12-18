<html>
   <head>
      <title>Add Firewall Rule</title>
      <link rel="StyleSheet" type="text/css" href="../fwdetail.css">
   </head>

   <body>
      <div id="content">
         <b>Add Port Forwarding Rule</b>
         <form action="admin/change_portforward.php" method="GET">
            <input type="hidden" name="action" value="add">
            <table id="access-table">
               <tr><th>Port</th><th>Protocol</th><th>Destination IP</th><th>Destination Port</tr>
               <tr>
                  <td><input name="port" size="6" maxlength="5"></td>
                  <td><select name="protocol">
                         <option value="tcp">tcp
                         <option value="udp">udp
                      </select>
                  </td>
                  <td><input name="destination_ip" size="16" maxlength="15"></td>
                  <td><input name="destination_port" size="6" maxlength="5"></td>
               </tr>
            </table>
            <br>
            <input type="submit" value="Add Rule">
         </form>
      </div>
   </body>
</html>
