#!/bin/bash
TMP_DIR=/tmp/p1b_tmp
REQUIRED_FILES="readme.txt team.txt create.sql load.sql queries.sql query.php violate.sql"

# usage
if [ $# -ne 1 ]
then
     echo "Usage: $0 Your_UID" 1>&2
     exit
fi

ZIP_FILE="P1B.zip"
FOLDER_NAME=$1

# clean any existing files
rm -rf ${TMP_DIR}
mkdir ${TMP_DIR}

# unzip the submission zip file 
if [ ! -f ${ZIP_FILE} ]; then
    echo "ERROR: Cannot find $ZIP_FILE, ensure this script is put in the same directory of your P1B.zip file. Otherwise check the zip file name" 1>&2
    rm -rf ${TMP_DIR}
    echo "rmd"
    exit 1
fi
unzip -q -d ${TMP_DIR} ${ZIP_FILE}
if [ "$?" -ne "0" ]; then 
    echo "ERROR: Cannot unzip ${ZIP_FILE} to ${TMP_DIR}"
    rm -rf ${TMP_DIR}
    exit 1
fi

# change directory to the grading folder
cd ${TMP_DIR}

if [ ! -d ${FOLDER_NAME} ];
then
echo "Check your folder name is EXACTLY the same as UID you typed"
rm -rf ${TMP_DIR}
exit 1
fi

cd ${FOLDER_NAME}

# check the existence of the required files
for FILE in ${REQUIRED_FILES}
do
    if [ ! -f ${FILE} ]; then
        echo "ERROR: Cannot find ${FILE} in the root folder of your zip file" 1>&2
	rm -rf ${TMP_DIR}
        exit 1
    fi
done

echo "Check File Successfully. Please upload your P1B.zip file to CCLE."
rm -rf ${TMP_DIR}
exit 0