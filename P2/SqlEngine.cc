/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include "Bruinbase.h"
#include "SqlEngine.h"
#include "BTreeIndex.h"
#include "limits.h"
#include "RecordFile.h"

using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

/**
   * these two part of function will be used more than one time in this class,
   * thus define these separate function to make the code easier reading.
   * checkCond is a function that can check the vector of conditions
   * and return true if pass the checking, or return false;
   */
bool checkCond(const vector<SelCond> &cond, int key, string &value)
{
  int diff;

  for (unsigned i = 0; i < cond.size(); i++) {
    // compute the difference between the tuple value and the condition value
    switch (cond[i].attr) {
      case 1:
        diff = key - atoi(cond[i].value);
            break;
      case 2:
        diff = strcmp(value.c_str(), cond[i].value);
            break;
    }

    // skip the tuple if any condition is not met
    switch (cond[i].comp) {
      case SelCond::EQ:
        if (diff != 0) return false;
            break;
      case SelCond::NE:
        if (diff == 0) return false;
            break;
      case SelCond::GT:
        if (diff <= 0) return false;
            break;
      case SelCond::LT:
        if (diff >= 0) return false;
            break;
      case SelCond::GE:
        if (diff < 0) return false;
            break;
      case SelCond::LE:
        if (diff > 0) return false;
            break;
    }
  }
  return true;
}

/**
   * checkPrintTuple is a function that can check the attr needed to output
   * accordingly print the tuple
   */
void checkPrintTuple(int attr, int key, string &value)
{
  switch (attr) {
    case 1:  // SELECT key
      fprintf(stdout, "%d\n", key);
          break;
    case 2:  // SELECT value
      fprintf(stdout, "%s\n", value.c_str());
          break;
    case 3:  // SELECT *
      fprintf(stdout, "%d '%s'\n", key, value.c_str());
          break;
  }
}

/**
   * this part of select function has been modified to adapt to the b+ tree application.
   */
RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning
  BTreeIndex bTreeIndex;
  IndexCursor cursor;
  // set two bounds of keys to define a range. if this range exist use the index to avoid scan.
  int searchKeyBottom = INT_MIN;
  int searchKeyTop = INT_MAX;

  RC     rc;
  int    key;
  string value;
  int    count;

  count = 0;
  rid.pid = rid.sid = 0;

  // open the table file, if failed, return error code.
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }

  /**
   * firstly, scan the conditions and concise them, form a conciseCond and two bounds;
   * the NE condition, and condition concerning value will be added to the vector<SelCond> conciseCond;
   * the GT, GE, LT, LE conditions will be processed into a closed range of key.
   * if there is invalid condition checked, directly end the query and avoid unnecessary read of pages.
   */
  vector<SelCond> conciseCond; // new conciseCond;
  // hasValCond will be true if there is value conditions in original condition set.
  // if true, the value must be read for condition checking.
  bool hasValCond = false;
  for(int i = 0; i < cond.size(); i++) {
    // if the condition considering Value.
    if (cond[i].attr == 2) {
      conciseCond.push_back(cond[i]);
      hasValCond = true;
    }
    else {
      int conValue = atoi(cond[i].value);
      switch (cond[i].comp) {
        case SelCond::EQ:
          if (conValue > searchKeyTop || conValue < searchKeyBottom) {
            goto exit_select;
          }//invalid condition
              searchKeyTop = searchKeyBottom = conValue;
              break;
        case SelCond::NE:
          if (conValue < searchKeyTop || conValue > searchKeyBottom) {
            conciseCond.push_back(cond[i]);
          }
          else if (conValue == searchKeyBottom && conValue == searchKeyTop) // conflict with point query
            goto exit_select; //invalid condition
          else if (conValue == searchKeyBottom)
            searchKeyBottom++;
          else if (conValue == searchKeyTop)
            searchKeyTop--;
              break;
        case SelCond::GT:
          conValue++;
        case SelCond::GE:
          if (conValue > searchKeyTop) goto exit_select;//invalid condition
              if (conValue > searchKeyBottom) searchKeyBottom = conValue;
              break;
        case SelCond::LT:
          conValue--;
        case SelCond::LE:
          if (conValue < searchKeyBottom) goto exit_select;//invalid condition
              if (conValue < searchKeyTop) searchKeyTop = conValue;
              break;
      }
    }
  }


  //open the index file
  //if the Index exist and opened successfully, and there is a range or point query on key, use the Index.
  if((searchKeyBottom != INT_MIN || searchKeyTop != INT_MAX) && bTreeIndex.open(table + ".idx", 'r') == 0) {

    rc = bTreeIndex.locate(searchKeyBottom, cursor);
    //if locate failed, end this query.
    if (rc != 0 && rc != RC_NO_SUCH_RECORD) {
      fprintf(stderr, "Error: while reading a entry from index %s\n", table.c_str());
      goto exit_select;
    }

    while(bTreeIndex.readForward(cursor,key,rid) == 0){
      // the key range as the restriction of continuing readForward.
      // the range is closed thus the searchKeyTop is contained if it existed.
      if(key > searchKeyTop) break;

      // this part to read from the table the values.
      // when there is conditions on value, or attr contians value or *, read the values.
      if(hasValCond == true || (attr != 4 && attr != 1)){
        if ((rc = rf.read(rid, key, value)) < 0) {
          fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
          goto exit_select;
        }
      }

      //check the conciseCond. if passed, add the count and print the tuple according to attr.
      if(checkCond(conciseCond, key, value)){
        count++;
        // check and print the tuple
        checkPrintTuple(attr, key, value);
      }
    }
    goto exit_select;
  }

else
    // scan the table file from the beginning
    // when there is no key range detected use the original method, which can be more efficient.
  while (rid < rf.endRid()) {
    // read the tuple
    if(hasValCond == true || (attr != 4)) {
      if ((rc = rf.read(rid, key, value)) < 0) {
        fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
        goto exit_select;
      }
    }

    // check the conditions on the tuple
    if (key > searchKeyTop || key < searchKeyBottom || !checkCond(conciseCond, key, value))
      goto next_tuple;

    // the condition is met for the tuple.
    // increase matching tuple counter
    count++;

    // print the tuple
    checkPrintTuple(attr, key, value);

    // move to the next tuple
    next_tuple:
    ++rid;
  }

  exit_select:
  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  rf.close();
  return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
  RecordFile rf;   // RecordFile containing the table
  ifstream infile;
  BTreeIndex bTreeIndex;

  RC     rc;
  string line;
  int    key;     
  string value;
  RecordId  rid;

    //open the loadfile
  infile.open(loadfile.c_str(),fstream::in);
  
  if(!infile){
    fprintf(stderr, "Error: open file %s filed\n", loadfile.c_str());
    return RC_FILE_OPEN_FAILED;
  }

    // open the table file
  if ((rc = rf.open(table + ".tbl", 'w')) < 0) {
    fprintf(stderr, "Error: create table %s failed\n", table.c_str());
    return rc;
  }

  if(index){
    if ((rc = bTreeIndex.open(table + ".idx",'w')) < 0){
      return rc;
    }
  }

  while(getline(infile, line)){
    if((rc = parseLoadLine(line, key, value)) < 0){
      fprintf(stderr, "Error: parsing %i - %s failed\n", key, value.c_str());
      return rc;
    }
    
    if((rc = rf.append(key, value, rid)) < 0){
      fprintf(stderr, "Error: append %i - %s to table %s failed\n", key, value.c_str(), table.c_str());
      goto exit_load;
    }

    if(index){
      if((rc = bTreeIndex.insert(key, rid)) < 0){
        fprintf(stderr, "Error: create Index %i - %s to table %s failed\n", key, value.c_str(), table.c_str());
        return rc;
      }
    }
  }

  rc = 0;

  exit_load:
  infile.close();
  rf.close();
  if(index){
    bTreeIndex.close();
  }
  return rc;
}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;
    
    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');
    
    // if there is nothing left, set the value to empty string
    if (c == 0) { 
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
