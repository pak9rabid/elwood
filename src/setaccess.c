#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met (interface, port, job), 
   // don't contruct or execute iptables command
   if (argc > 3)
   {
      char fwCmd[128] = IPTABLES;
      strcat(fwCmd, " -A INPUT -i ");
      strcat(fwCmd, argv[1]);
      strcat(fwCmd, " -p tcp --dport ");
      strcat(fwCmd, argv[2]);
      strcat(fwCmd, " -j ");
      strcat(fwCmd, argv[3]);

      system(fwCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: setaccess <interface> <port> <job>\n\n");

   return 0;
}
      
