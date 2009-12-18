#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // Verify correct number of args are present
   if (argc > 1)
   {
      // Contruct and execute iptables command
      char fwCmd[128] = IPTABLES;

      if (strcmp(argv[1], "PREROUTING") == 0)
         strcat(fwCmd, " -t nat -F PREROUTING");
      else if (strcmp(argv[1], "POSTROUTING") == 0)
         strcat(fwCmd, " -t nat -F POSTROUTING");
      else
      {
         strcat(fwCmd, " -F ");
         strcat(fwCmd, argv[1]);
      }
      
      system(fwCmd);
   }

   // Print usage synopsis
   else
      printf("\nUsage: flushchain <chain>\n\n");

   return(0);
}
