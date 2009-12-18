#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main(int argc, char **argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   char fwCmd[128] = IPTABLES;

   // Verify correct number of args are present
   if (argc > 2)
   {
      // Contruct and execute iptables command
      if (strcmp(argv[1], "PREROUTING") == 0) 
         strcat(fwCmd, " -t nat -P PREROUTING");
      else if (strcmp(argv[1], "POSTROUTING") == 0)
         strcat(fwCmd, " -t nat -P POSTROUTING");
      else
      {
         strcat(fwCmd, " -P ");
         strcat(fwCmd, argv[1]);
      }

      strcat(fwCmd, " ");
      strcat(fwCmd, argv[2]);
      
      system(fwCmd);
   }

   // Print usage synopsis
   else
      printf("\nUsage: setpolicy <chain> <policy>\n\n");

   return(0);
}
