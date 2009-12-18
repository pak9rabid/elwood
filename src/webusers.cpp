#include <fstream>
#include "webusers.h"

using namespace std;

//////////////////
// Constructors //
//////////////////

webusers::webusers()
{
   // Create an emtpy list of users and set groupsFilePath
   // to nothing
   admins.clear();
   users.clear();

   groupsFilePath = "";
}

webusers::webusers(string filePathIn)
{
   // Store groupsFilePath in object
   groupsFilePath = filePathIn;

   // Open file for reading
   ifstream in_file;
   in_file.open(groupsFilePath.c_str());

   // If file opened successfully, continue with reading file contents
   if (in_file.is_open())
   {
      // Temp vars
      char c;
      string temp;

      // Initialize user lists
      admins.clear();
      users.clear();

      // Pointer used to point to the current user group we are working on
      vector<string> *groupPtr;

      for (int i=0 ; i<2 ; i++)
      {
         // Set group pointer to the group that we are working on
         if (i == 0)
            groupPtr = &admins;
         else
            groupPtr = &users;

         // Initialize temp vars
         temp = "";
         c = in_file.get();

         // Find the group name
         while (c != ':')
         {
            temp += c;
            c = in_file.get();
         }

         // Store group name as the 0th element in the group list
         groupPtr->push_back(temp);

         // Populate the rest of the group list
         while (in_file.peek() != '\n' && in_file.peek() != EOF)
         {
            in_file >> temp;
            groupPtr->push_back(temp);
         }

         // Move the stream pointer up one char to the next line
         in_file.get();
      }
      
      // Close file stream
      in_file.close();
   }
}

///////////////
// Accessors //
///////////////

const string webusers::getGroup(string user)
{
   // Pointer used to point to the current user we are working on
   vector<string> *groupPtr;

   for (unsigned int i=0 ; i<2 ; i++)
   {
      if (i == 0)
         groupPtr = &admins;
      else
         groupPtr = &users;

      for (unsigned int j=1 ; j<groupPtr->size() ; j++)
      {
         if (groupPtr->at(j) == user)
            return groupPtr->at(0);
      }
   }

   return "-1";
}

const vector<string> webusers::getAdmins()
{
   return admins;
}

const vector<string> webusers::getUsers()
{
   return users;
}

const string webusers::getFilePath()
{
   return groupsFilePath;
}

const bool webusers::userExists(string user)
{
   // Check to see if user exists in one of the groups
   bool doesUserExist = false;
   vector<string> *groupPtr;

   for (unsigned int i=0 ; i<2 ; i++)
   {
      if (i == 0)
         groupPtr = &admins;
      else
         groupPtr = &users;

      for (unsigned int j=0 ; j<groupPtr->size() ; j++)
      {
         if (groupPtr->at(j) == user)
            doesUserExist = true;
      }
   }

   return doesUserExist;
}

///////////////
// Modifiers //
///////////////

void webusers::addUser(string user)
{
   // If user does not exist, push onto the users list
   if (!userExists(user))
      users.push_back(user);
}

void webusers::addUser(string user, string group)
{
   // If user does not exist, add user to the specified group
   if (!userExists(user))
   {
      if (group == "admins")
         admins.push_back(user);
      else
         users.push_back(user);
   }
}

void webusers::changeGroup(string user)
{
   // Check to see if the user exists in any of the groups
   int location;

   if (userExists(user))
   {
      location = findUser(user, admins);

      if (location != -1)
      {
         admins.erase(admins.begin() + location);
         users.push_back(user);
      }
      else
      {
         location = findUser(user, users);
         users.erase(users.begin() + location);
         admins.push_back(user);
      }
   }
}

void webusers::changeGroup(string user, string group)
{
   // Check to see if the user exists in any of the groups
   int location;

   if (userExists(user))
   {
      // Find user and remove
      location = findUser(user, admins);

      if (location != -1)
         admins.erase(admins.begin() + location);
      else
      {
         location = findUser(user, users);
         users.erase(users.begin() + location);
      }

      // Once user is removed, add to specified group
      if (group == "admins")
         admins.push_back(user);
      else
         users.push_back(user);
   }
}

void webusers::removeUser(string user)
{
   // Check to see if the user exists in any of the groups
   // and erase if they do
   int location;

   if (userExists(user))
   {
      location = findUser(user, admins);

      if (location != -1)
         admins.erase(admins.begin() + location);
      else
      {
         location = findUser(user, users);
            users.erase(users.begin() + location);
      }
   }
}

void webusers::setFilePath(string filePathIn)
{
   // If filePathIn is not empty, set groupsFilePath
   if (filePathIn != "")
      groupsFilePath = filePathIn;
}

void webusers::writeGroupsFile()
{
   // Write groups file to groupsFilePath
   ofstream out_file;
   out_file.open(groupsFilePath.c_str());

   if (out_file.is_open())
   {
      vector<string> *groupPtr;

      for (unsigned int i=0 ; i<2 ; i++)
      {
         if (i == 0)
            groupPtr = &admins;
         else
            groupPtr = &users;

         out_file << groupPtr->at(0) << ":";

         for (unsigned int j=1 ; j<groupPtr->size() ; j++)
         {
            out_file << groupPtr->at(j);

            if (j != groupPtr->size() - 1)
               out_file << " ";
         }

         if (i == 0)
            out_file << endl;
      }
      // Close file stream
      out_file.close();
   }
}

void webusers::writeGroupsFile(string filePathIn)
{
   // Write groups file to filePathIn
   ofstream out_file;
   out_file.open(filePathIn.c_str());

   if (out_file.is_open())
   {
      vector<string> *groupPtr;

      for (unsigned int i=0 ; i<2 ; i++)
      {
         if (i == 0)
            groupPtr = &admins;
         else
            groupPtr = &users;

         out_file << groupPtr->at(0) << ":";

         for (unsigned int j=1 ; j<groupPtr->size() ; j++)
         {
            out_file << groupPtr->at(j);

            if (j != groupPtr->size() - 1)
               out_file << " ";
         }

         if (i == 0)
            out_file << endl;
      }
      // Close file stream
      out_file.close();
   }
}

//////////////////////////////
// Private helper functions //
//////////////////////////////

const int webusers::findUser(string user, vector<string> group)
{
   // Check to see if the user exists in the specified group
   // and return it's location in the vector, or -1 if use is not found
   for (unsigned int i=0 ; i<group.size() ; i++)
   {
      if (group.at(i) == user)
         return i;
   }

   return -1;
}
