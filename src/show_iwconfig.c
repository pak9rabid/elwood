#include <stdio.h>
#include <unistd.h>
#include "config.h"

int main()
{
   char runCmd[128] = IWCONFIG;
   strcat(runCmd, " ");
   strcat(runCmd, WLAN_IF);
   setreuid(geteuid(), geteuid());
   system(runCmd);

   return(0);
}
