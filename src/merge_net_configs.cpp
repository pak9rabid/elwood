#include <iostream>
#include <fstream>
#include <string>
#include <stdlib.h>
#include <unistd.h>
#include "config.h"

using namespace std;

string getConfig(string fileIn);
void   writeConfig(string configIn, string configFileIn);

int main()
{
   // Set user id
   setreuid(geteuid(), geteuid());

   //////////
   // Read in individual network configs
   
   string output;
   string wanConfigFile  = ELWOOD_CONFIG_DIR;
   string lanConfigFile  = ELWOOD_CONFIG_DIR;
   string wlanConfigFile = ELWOOD_CONFIG_DIR;

   wanConfigFile  += "/inet.d/wan.conf";
   lanConfigFile  += "/inet.d/lan.conf";
   wlanConfigFile += "/inet.d/wlan.conf";

   output  = getConfig(wanConfigFile);
   //output += "\n";
   output += getConfig(lanConfigFile);

   // Get wlan config only if there's a wlan interface present
   if (WLAN_IF != "none")
      output += getConfig(wlanConfigFile);

   // Done reading configs
   //////////

   // Add loopback interface
   output += "# lo\n";
   output += "auto lo\n";
   output += "iface lo inet loopback";

   // Write interfaces file
   writeConfig(output, INTERFACES);

   return(0);
}

///////////////
// Functions //
///////////////

string getConfig(string fileIn)
//Preconditions:  fileIn must be a string of a path to a network config file
//Postconditions: Contents of fileIn is returned
{
   ifstream inFile;

   string line;
   string output;

   // Open fileIn
   inFile.open(fileIn.c_str());

   // Grab contents of file if it's open
   if (inFile.is_open())
   {
      getline(inFile, line);
      output = line + "\n";

      while (!inFile.eof())
      {
         getline(inFile, line);
         output += line + "\n";
      }

      // Close file
      inFile.close();
   }
   else
      cout << "Could not open file " + fileIn;

   return output;
}

void writeConfig(string configIn, string configFileIn)
// Preconditions:  configFileIn must be a path to a writable file
// Postconditions: configIn is written to configFileIn
{
   // Open configFileIn
   ofstream outFile;
   
   outFile.open(configFileIn.c_str());

   // Write contents of configIn to configFileIn
   if (outFile.is_open())
   {
      outFile << configIn;

      // Close file
      outFile.close();
   }
   else
      cout << "Could not open file " + configFileIn;
}
