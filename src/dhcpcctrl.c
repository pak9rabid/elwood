#include <stdlib.h>
#include <unistd.h>
#include <string.h>
#include "config.h"

int main(int argc, char** argv)
{
   // Set uid
   setreuid(geteuid(), geteuid());

   // If minimum args are not met,
   // don't construct or execute dhcpcd command
   if ( (argc > 2) &&
        (strcmp(argv[1], "start") == 0 || strcmp(argv[1], "stop") == 0) ) 
   {
      // Construct command to stop or start dhcp client
      char dhcpcdCmd[128] = DHCPCD;

      if ( strcmp(argv[1], "start") == 0 )
      {
         strcat(dhcpcdCmd, " -t 10 -d ");

	 // Append hostname, if specified
	 if (argc > 3)
         {
	    strcat(dhcpcdCmd, "-h ");
	    strcat(dhcpcdCmd, argv[3]);
	    strcat(dhcpcdCmd, " ");
	 }
      }
      else
         strcat(dhcpcdCmd, " -k ");

      strcat(dhcpcdCmd, argv[2]);

      // Execute command
      system(dhcpcdCmd);
   }
   else
      // Print usage synopsis
      printf("\nUsage: dhcpcctrl <start|stop> <interface> [hostname]\n\n") ;

   return 0;
}
