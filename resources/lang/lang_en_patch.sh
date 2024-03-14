#!/bin/sh

FILE=$1

echo "Patching $FILE"

DELIM="\([^[:alpha:]]\)"

sed -i "/^msgid/!s/${DELIM}angeltype/\1volunteer type/g" $FILE
sed -i "/^msgid/!s/${DELIM}Angeltype/\1Volunteer type/g" $FILE

sed -i "/^msgid/!s/${DELIM}Engelsystem${DELIM}/\1volunteer system\2/g" $FILE

sed -i "/^msgid/!s/${DELIM}heaven angels${DELIM}/\1volunteer managers\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}drop by heaven${DELIM}/\1drop by the volunteer office\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}heavens desk${DELIM}/\1volunteer office\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}[Hh]eaven${DELIM}/\1volunteer managers\2/g" $FILE

sed -i "/^msgid/!s/${DELIM}an [Aa]ngel${DELIM}/\1a volunteer\2/g" $FILE

sed -i "/^msgid/!s/${DELIM}angel${DELIM}/\1volunteer\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}Angel${DELIM}/\1Volunteer\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}angels${DELIM}/\1volunteers\2/g" $FILE
sed -i "/^msgid/!s/${DELIM}Angels${DELIM}/\1Volunteers\2/g" $FILE

grep -Inri "angel" $FILE | grep -v ":msgid"
grep -Inri "heaven" $FILE | grep -v ":msgid"
grep -Inri "himmel" $FILE | grep -v ":msgid"

echo "done"
