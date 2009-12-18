#include <iostream>
#include <stdlib.h>
#include <unistd.h>
#include "webusers.h"
#include "config.h"

using namespace std;

void printUsage();
// Preconditions: None
// Postcondition: Prints usage synposis

int main(int argc, char **argv)
{
   // Set constants
   const string USERS = USERS_FILE;

   // Set uid
   setreuid(geteuid(), geteuid());

   // Check to make sure proper args are typed in
   if (argc > 2)
   {
      // Contruct stem command
      string htpasswdCmd(HTPASSWD);

      // Store required args into string vars
      string operation(argv[1]);
      string user(argv[2]);

      // Create object to store/modify groups
      webusers currentUsers(GROUPS_FILE);

      // Determine which operation is specified
      // and act accordingly
      if (operation == "add")
      {
         // Check that the password arg was entered
         if (argc > 3)
         {
            // Complete shell command to add a new user
            string password(argv[3]);
            htpasswdCmd += " -b " + USERS + " " + user + " " + password;
            
            // Execute shell command to add user
            system(htpasswdCmd.c_str());

            // Add new user to the groups file
            currentUsers.addUser(user);
            currentUsers.writeGroupsFile();
            cout << "User " << user << " added" << endl;
         }
         else
            // Print usage synopsis
            printUsage();
      }
      else if (operation == "remove")
      {
         // Complete shell command to remove a user
         htpasswdCmd += " -D " + USERS + " " + user;

         // Execute shell command to remove user
         system(htpasswdCmd.c_str());

         // Remove user from the groups file
         currentUsers.removeUser(user);
         currentUsers.writeGroupsFile();
         cout << endl << "User " << user << " deleted" << endl;
      }
      else if (operation == "chgroup")
      {
         // If there is a group entered, set user to be associated with 
         // that group
         if (argc > 3)
         {
            string group(argv[3]);
            currentUsers.changeGroup(user, group);
         }
         else
            currentUsers.changeGroup(user);

         currentUsers.writeGroupsFile();
         cout << endl << "Changed group for " << user << endl;
      }
      else if (operation == "chpass")
      {
         // Check to see if the required args were entered
         if (argc > 3)
         {
            // Check to see if the user exists
            if (currentUsers.userExists(user))
            {
               // Get the password and construct the htpasswd command
               string password(argv[3]);
               htpasswdCmd += " -b " + USERS + " " + user + " " + password;

               // Execute htpasswd command
               system(htpasswdCmd.c_str());
            }
            else
               cout << endl << "User " << user << " not found, no changes made" << endl;
            
         }
         else
            // Print usage synopsis
            printUsage();
         
      }
      else
         // Print usage synopsis
         printUsage();
   }  
   else
      // Print usage synopsis
      printUsage();
}

///////////////
// Functions //
///////////////

void printUsage()
{
   cout << endl;
   cout << "Usage: user_htpasswd <add> <user> <password>" << endl;
   cout << "       user_htpasswd <remove> <user>" << endl;
   cout << "       user_htpasswd <chgroup> <user> [group]" << endl;
   cout << "       user_htpasswd <chpass> <user> <password>" << endl;
   cout << endl;
}
