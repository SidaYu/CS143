#include <cstring>
#include "BTreeNode.h"

using namespace std;



BTLeafNode::BTLeafNode(){
	for(int i = 0;i < PageFile::PAGE_SIZE; i++){
		buffer[i] = '\0';
	}
	int count = 0;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
	memcpy(buffer + offset, &count, sizeof(int));
    // add the count to the final 4 bytes of the page.
}
/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{ 
	RC rc;
	if((rc = pf.read(pid, buffer)) < 0) {
		return rc;
	}
	return 0; 
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{ 
	RC rc;
	if((rc = pf.write(pid, buffer)) < 0) {
		return rc;
	}
	return 0; 
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{ 
	int count;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
	memcpy(&count, buffer + offset, sizeof(int));
	return count; 
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
    RC rc;
    int count = getKeyCount();
    if(count >= MAX_NUM_LEAF){
        return RC_NODE_FULL;
    }
    int eid = -1;
    if((rc = locate(key,eid)) < 0 || eid < 0){
        //not found the mid-entry value, append the (key, rid) pair to end.
        int offset = count * LEAF_ENTRY_SIZE;
        memcpy(buffer + offset, &rid.pid, sizeof(PageId));
        offset += sizeof(PageId);
        memcpy(buffer + offset, &rid.sid, sizeof(int));
        offset += sizeof(int);
        memcpy(buffer + offset, &key, sizeof(int));
    } else{
        //find the mid entry value, insert the (key, rid) pair.
        int offset = (eid - 1) * LEAF_ENTRY_SIZE;
        //shift all the record.
        for(int i = count * LEAF_ENTRY_SIZE -1 ; i >= offset; i--){
            buffer[i + LEAF_ENTRY_SIZE] = buffer[i];
        }
        //insert the (key, rid) pair.
        memcpy(buffer + offset, &rid.pid, sizeof(PageId));
        offset += sizeof(PageId);
        memcpy(buffer + offset, &rid.sid, sizeof(int));
        offset += sizeof(int);
        memcpy(buffer + offset, &key, sizeof(int));
    }
    //update the count;
    count ++;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));
    return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid, 
                              BTLeafNode& sibling, int& siblingKey)
{
    RC rc;
    int count = getKeyCount();
    int split_eid = count/2;

    int splitKey;
    RecordId splitRid;

    for(int i = split_eid; i < count; i++){
        int offset = i * LEAF_ENTRY_SIZE;
        memcpy(&splitRid.pid, buffer + offset, sizeof(PageId));
        offset += sizeof(PageId);
        memcpy(&splitRid.sid, buffer + offset, sizeof(int));
        offset += sizeof(int);
        memcpy(&splitKey, buffer + offset, sizeof(int));
        if((rc = sibling.insert(splitKey, splitRid)) < 0){
            return rc;
        };
        //clear the buffer.
        for(int j = i * LEAF_ENTRY_SIZE; j < (i+1) * LEAF_ENTRY_SIZE; j++){
            buffer[j] = '\0';
        }
        //set siblingKey.
        if(i == split_eid){
            siblingKey = splitKey;
        }
    }
    //reset the count.
    count = split_eid;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));

    if(key < siblingKey){
        if((rc = this->insert(key, rid)) < 0){
            return rc;
        };
    } else{
        if((rc = sibling.insert(key, rid)) < 0){
            return rc;
        };
    }
    sibling.setNextNodePtr(this->getNextNodePtr());
    return 0;
}

/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{
    int count = getKeyCount();
    int key;
    int counter = 0;

    for(int i = sizeof(RecordId); i < count * LEAF_ENTRY_SIZE; i += LEAF_ENTRY_SIZE){
        counter ++;
        memcpy(&key, buffer+i, sizeof(int));
        if(key >= searchKey){
            eid = counter;
            return 0;
        }
    } //eid from 1... to count.
    return RC_NO_SUCH_RECORD; //-1012
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{
    if(eid > getKeyCount() || eid < 0){
        return RC_INVALID_CURSOR;
    }
    int offset = (eid - 1) * LEAF_ENTRY_SIZE;
    memcpy(&rid.pid, buffer + offset, sizeof(PageId));
    offset += sizeof(PageId);
    memcpy(&rid.sid, buffer + offset, sizeof(int));
    offset += sizeof(int);
    memcpy(&key, buffer + offset, sizeof(int));
    return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{ 
	PageId pid;
    int offset = PageFile::PAGE_SIZE - sizeof(int) - sizeof(PageId);
	memcpy(&pid, buffer + offset, sizeof(PageId));
	return pid;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{ 
	if (pid < 0) {
		return RC_INVALID_PID;
	}
    int offset = PageFile::PAGE_SIZE - sizeof(int) - sizeof(PageId);
	memcpy(buffer + offset, &pid, sizeof(PageId));
	return 0; 
}

/****************************************************************************/

/*
 * constructor
 */
BTNonLeafNode::BTNonLeafNode(){
	for(int i = 0;i < PageFile::PAGE_SIZE; i++){
		buffer[i] = '\0';
	}
	int count = 0;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));
    // add the count to the final 4 bytes of the page.
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{ 
	RC rc;
	if((rc = pf.read(pid, buffer)) < 0) {
		return rc;
	}
	return 0; 
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{ 
	RC rc;
	if((rc = pf.write(pid, buffer)) < 0) {
		return rc;
	}
	return 0; 
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{ 
	int count;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
	memcpy(&count, buffer + offset, sizeof(int));
	return count; 
}


/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{
    RC rc;
    int count = getKeyCount();
    if(count >= MAX_NUM_NONLEAF){
        return RC_NODE_FULL;
    }
    int eid = -1;
    if((rc = locate(key,eid)) < 0 || eid < 0){
        //not found the mid-entry value, append the (key, rid) pair to end.
        int offset = count * NONLEAF_ENTRY_SIZE + (int)sizeof(PageId);
        memcpy(buffer + offset, &key, sizeof(int));
        offset += sizeof(int);
        memcpy(buffer + offset, &pid, sizeof(PageId));
    } else{
        //find the mid entry value, insert the (key, rid) pair.
        int offset = (eid - 1) * NONLEAF_ENTRY_SIZE + (int)sizeof(PageId);
        //shift all the record.
        for(int i = count * NONLEAF_ENTRY_SIZE + (int)sizeof(PageId) - 1 ; i >= offset; i--){
            buffer[i + NONLEAF_ENTRY_SIZE] = buffer[i];
        }
        //insert the (key, rid) pair.
        memcpy(buffer + offset, &key, sizeof(int));
        offset += sizeof(int);
        memcpy(buffer + offset, &pid, sizeof(PageId));
    }
    //update the count;
    count ++;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));
    return 0;
}

/**
 * locate a key within a nonleaf node
 * @param int searchKey[IN] the key to search for
 * @param int& eid[OUT] the position of the eid. first entry has position 1
 * return 0 if successful. error code
**/
RC BTNonLeafNode::locate(int searchKey, int& eid)
{
    int count = getKeyCount();
    int key;
    int counter = 0;
    for (int i = sizeof(PageId); i < count * NONLEAF_ENTRY_SIZE; i += NONLEAF_ENTRY_SIZE) {
        counter++;
        memcpy(&key, buffer+i, sizeof(int));
        if(key>=searchKey) {
            eid = counter;
            return 0;
        }
    }
    return RC_NO_SUCH_RECORD;
}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
    RC rc;
    int count = getKeyCount();
    int split_eid= (count + 1)/2;
    //split
    int splitKey;
    PageId splitPid;
    PageId firstPid; //the firstPid in sibling Node.

    for(int i = split_eid - 1; i < count; i++){
        int offset = i * NONLEAF_ENTRY_SIZE + sizeof(PageId);
        memcpy(&splitKey, buffer + offset, sizeof(int));
        memcpy(&splitPid, buffer + offset + sizeof(int), sizeof(PageId));
        //set midKey.
        if(i == split_eid - 1){
            midKey = splitKey;
            firstPid = splitPid;
            //clear the buffer.
            for(int j = offset; j < offset + NONLEAF_ENTRY_SIZE; j++){
                buffer[j] = '\0';
            }
            continue;
        }

        if(sibling.getKeyCount() <= 0){
            sibling.initializeRoot(firstPid,splitKey,splitPid);
        }else if((rc = sibling.insert(splitKey, splitPid)) < 0){
            return rc;
        };
        //clear the buffer.
        for(int j = i * NONLEAF_ENTRY_SIZE + sizeof(PageId); j < (i + 1) * NONLEAF_ENTRY_SIZE + sizeof(PageId); j++){
            buffer[j] = '\0';
        }
    }
    //reset the count.
    count = split_eid - 1;
    int offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));

    //insert the key
    if(key < midKey){
        if((rc = this->insert(key, pid)) < 0){
            return rc;
        };
    } else{
        if((rc = sibling.insert(key, pid)) < 0){
            return rc;
        };
    }

    return 0;
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{
    int count = getKeyCount();
    int key;

    for(int i = sizeof(PageId); i < count * NONLEAF_ENTRY_SIZE; i += NONLEAF_ENTRY_SIZE){
        memcpy(&key, buffer + i, sizeof(int));
        if(key == searchKey){
            memcpy(&pid, buffer + i + sizeof(int), sizeof(PageId));
            return 0;
        }else if(key > searchKey){
            memcpy(&pid, buffer + i - sizeof(PageId), sizeof(PageId));
            return 0;
        }
    }
    //if the key larger than the final key.
    int offset = count * NONLEAF_ENTRY_SIZE;
    memcpy(&pid, buffer + offset, sizeof(PageId));
    return 0;
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{
    if(pid1 < 0 || pid2 < 0){
        return RC_INVALID_PID;
    }

    int offset = 0;
    memcpy(buffer + offset, &pid1, sizeof(PageId));
    offset += sizeof(PageId);
    memcpy(buffer + offset, &key, sizeof(int));
    offset += sizeof(int);
    memcpy(buffer + offset, &pid2, sizeof(PageId));

    int count = 1;
    offset = PageFile::PAGE_SIZE - sizeof(int);
    memcpy(buffer + offset, &count, sizeof(int));
    return 0;
}
