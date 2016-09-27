/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"
#include <cstring>

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
    treeHeight = 0;
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
    RC   rc;
    // open the page file
    if ((rc = pf.open(indexname, mode)) < 0) return rc;

    //read the first page which contain the info. of rootPid and treeHeight.
    char buffer[PageFile::PAGE_SIZE];
    if(pf.endPid() > 0){
        if((rc=pf.read(0,buffer))<0) {
            return rc;
        }
        //copy root pid and height of tree to the tree index object
        memcpy(&rootPid, buffer, sizeof(PageId));
        memcpy(&treeHeight, buffer + sizeof(PageId), sizeof(int));
    }
    return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
    RC   rc;
    //write the current rootPid and treeHeight to the 1st page when Index closed.
    char buffer[PageFile::PAGE_SIZE];
    memset(buffer, '\0', sizeof(buffer));
    memcpy(buffer, &rootPid, sizeof(PageId));
    memcpy(buffer + sizeof(PageId), &treeHeight, sizeof(int));
    if((rc=pf.write(0, buffer))<0) {
        return rc;
    }
    // close the page file
    if ((rc = pf.close()) < 0) return rc;
    return 0;
}

/*
 * to implement the recursive form of insert, use this helper.
 * @return code. 0 if no error
 * @return code. -1 if insert failed
 * @return code. 1 if it is a overflow case, need to handle.
 */
RC BTreeIndex::insertHelper(int& key, const RecordId& rid,
                            int height, PageId currentPid, int& overflowKey, PageId& overflowPid) {
    RC rc;
    if (treeHeight == height){
        //when it is in the leafNode level. the final case.
        BTLeafNode btLeafNode = BTLeafNode();
        btLeafNode.read(currentPid,pf);
        if(btLeafNode.getKeyCount() < btLeafNode.MAX_NUM_LEAF){
            if((rc = btLeafNode.insert(key,rid)) < 0) return rc;
            btLeafNode.write(currentPid,pf);
            return 0;
        } else{
            //overflow, need to split.
            BTLeafNode sibling = BTLeafNode();
            int siblingKey = -1;
            if((rc = btLeafNode.insertAndSplit(key,rid,sibling,siblingKey)) < 0) return rc;
            overflowKey = siblingKey;
            overflowPid = pf.endPid();
            sibling.setNextNodePtr(btLeafNode.getNextNodePtr());
            btLeafNode.setNextNodePtr(overflowPid);
            btLeafNode.write(currentPid,pf);
            sibling.write(overflowPid,pf);
            return 1; //need a parent node.
        }
    } else{
        BTNonLeafNode btNonLeafNode = BTNonLeafNode();
        btNonLeafNode.read(currentPid,pf);
        //to locate the ChildPtr, the next level entry.
        PageId childPid;
        btNonLeafNode.locateChildPtr(key,childPid);
        int status = insertHelper(key, rid, height + 1, childPid,overflowKey,overflowPid);
        //handle the status
        if(status == 1){
            //handle the overflow case.
            if(btNonLeafNode.getKeyCount() < btNonLeafNode.MAX_NUM_NONLEAF){
                //simple insert.
                if((rc = btNonLeafNode.insert(overflowKey,overflowPid)) < 0) return rc;
                btNonLeafNode.write(currentPid,pf);
                return 0;
            } else{
                //still overflow, continue to split.
                BTNonLeafNode sibling;
                int midKey = -1;
                if((rc = btNonLeafNode.insertAndSplit(overflowKey, overflowPid, sibling, midKey)) < 0) return rc;
                overflowKey = midKey;
                overflowPid = pf.endPid();
                btNonLeafNode.write(currentPid, pf);
                sibling.write(overflowPid, pf);
                return 1;
            }
        } else{
            //if failed.
            return status;
        }


    }
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
    if(treeHeight == 0){
        BTLeafNode btLeafNode = BTLeafNode();
        btLeafNode.insert(key,rid);
        rootPid = pf.endPid();
        //leave the first page blank for treeHeight & rootPid
        //a little bit tricky, but use A LeafNode object is convenient.
        if(rootPid==0) {
            BTLeafNode emptyNode = BTLeafNode();
            emptyNode.write(rootPid,pf);
            rootPid = pf.endPid();
        }
        btLeafNode.write(rootPid,pf);
        treeHeight ++;
        return 0;
    }else{
        int initialHeight = 1;
        //recurse from root;
        int overflowKey;
        int overflowPid;
        int status = insertHelper(key, rid, initialHeight, rootPid, overflowKey, overflowPid);
        if(status <= 0){
            return status;
        } else if(status == 1){
            //overflow case.
            BTNonLeafNode newRoot = BTNonLeafNode();
            newRoot.initializeRoot(rootPid, overflowKey, overflowPid);
            PageId newRootId = pf.endPid();
            newRoot.write(newRootId,pf);
            rootPid = newRootId;
            treeHeight ++;
        }

    }

    return 0;
}

/**
 * Run the standard B+Tree key search algorithm and identify the
 * leaf node where searchKey may exist. If an index entry with
 * searchKey exists in the leaf node, set IndexCursor to its location
 * (i.e., IndexCursor.pid = PageId of the leaf node, and
 * IndexCursor.eid = the searchKey index entry number.) and return 0.
 * If not, set IndexCursor.pid = PageId of the leaf node and
 * IndexCursor.eid = the index entry immediately after the largest
 * index key that is smaller than searchKey, and return the error
 * code RC_NO_SUCH_RECORD.
 * Using the returned "IndexCursor", you will have to call readForward()
 * to retrieve the actual (key, rid) pair from the index.
 * @param key[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @return 0 if searchKey is found. Othewise an error code
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
    //search from the first level;
    return locateHelper(searchKey, 1, rootPid, cursor);
}

RC BTreeIndex::locateHelper(int searchKey, int height, PageId currentPid, IndexCursor& cursor){
    RC rc;
    if(treeHeight == height){
        BTLeafNode btLeafNode = BTLeafNode();
        //cout << "read btLeafNode final" << endl;
        btLeafNode.read(currentPid, pf);
        int eid;
        rc = btLeafNode.locate(searchKey,eid);
        cursor.pid = currentPid;
        cursor.eid = eid;
        return rc;
    } else{
        BTNonLeafNode btNonLeafNode = BTNonLeafNode();
        //cout << "read btLeafNode processing" << endl;
        btNonLeafNode.read(currentPid,pf);
        int currentPid;
        btNonLeafNode.locateChildPtr(searchKey, currentPid);
        //cout << "currentPid" << currentPid << endl;
        rc = locateHelper(searchKey,height + 1, currentPid, cursor);
        return rc;
    }
}

/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
    RC rc;
    BTLeafNode btLeafNode;
    //if the last entry is the final one, return -1, means end.
    if(cursor.pid == 0) return -1;

    if((rc = btLeafNode.read(cursor.pid, pf)) < 0){
        return rc;
    }
    if((rc = btLeafNode.readEntry(cursor.eid, key, rid)) < 0){
        return rc;
    }

    //after reading move to the next entry in the same leaf
    cursor.eid++;
    //if we had just recovered the last entry in the node, get the pointer to next leaf
    if(cursor.eid > btLeafNode.getKeyCount()) {
        cursor.pid = btLeafNode.getNextNodePtr();
        cursor.eid=1;
    }

    return 0;
}
