#include <stdlib.h>
#include <stdio.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute ifup command
   if (argc > 1)
   {
      // Construct ifup command to bring up
      // specified interface
      char ifupCmd[128] = IFUP;
      strcat(ifupCmd, " ");
      strcat(ifupCmd, argv[1]);

      // Execute command
      system(ifupCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: intup <interface>\n\n");

   return 0;
}
