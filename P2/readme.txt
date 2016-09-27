name: Sida Yu
e-mail: sidayu@ucla.edu

- This is the final part of Project 2, I modified the SqlEngine.cc 
- After testing, all the functions in this part can fulfill the designed tasks.

- Some spec of the project:
	* Due to the design of BtreeNode.cc When build the Index, there are 84 records in a leafNode and 99 records in a NonLeafNode. This two constants were defined in BtreeNode.h, according to a principle that fully use the space. And I think this two num needn’t to be the same in the practical situation.
	* The RootId and ThreeHeight are stored in the first page of .idx file. Thus, this will cost one page read. my system is okey with all the test scripts and their readPage count requirement.
	* To optimize the system and reduce the page read count, make a trade off between the original scan method and the B+ tree Index method, I use the following classification.
		- use the key as the restriction of readForward.
		- to extract the upper bound and the lower bound of key from the condition set.
		- if there is no bound, just use the original scan, which can obviously reduce the unnecessary page read. if there is a range of key, use the Index.
		- if there exist condition considering value, or the attributes returned need the value, read the value. or it is unnecessary to read from the table.
		- if just select the count of whole table, use the scan method, it is efficient.

— Only the SqlEngine.cc, BTreeNode.h, BTreeNode.cc, BTreeIndex.h and BTreeIndex.cc were modified in this version.

Thanks~