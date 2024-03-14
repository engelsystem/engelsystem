#!/bin/sh

FILE=$1

echo "Patching $FILE"

DELIM="\([^[:alpha:]]\)"

sed -i "s/${DELIM}Engeltyp/\1Helferinnentyp/g" $FILE
sed -i "s/${DELIM}Engelliste/\1Helferinnenliste/g" $FILE
sed -i "s/${DELIM}Engelsystem/\1Helferinnensystem/g" $FILE

sed -i "s/${DELIM}Himmel-Engeln${DELIM}/\1der Helferinnen-Orga\2/g" $FILE
sed -i "s/${DELIM}Himmelsschreibtisch/\1Helferinnenraum/g" $FILE
sed -i "s/${DELIM}\(im\|zum\) Himmel${DELIM}/\1\2 Helferinnenraum\3/g" $FILE
sed -i "s/${DELIM}den Himmel${DELIM}/\1die Helferinnen-Orga\2/g" $FILE
sed -i "s/${DELIM}vom Himmel${DELIM}/\1von der Helferinnen-Orga\2/g" $FILE

sed -i "s/${DELIM}Engeln${DELIM}/\1Helferinnen\2/g" $FILE
sed -i "s/\(enötigten\?\|Alle\|Freie\|keine\|Aktive\|arbeitende\|Ankommende\|Mehr\|folgenden\|viele\|0\|%s\|Admins\) Engel${DELIM}/\1 Helferinnen\2/g" $FILE
sed -i "s/${DELIM}Engel \(kann\|hat\|ist\|wurde\)${DELIM}/\1Helferin \2\3/g" $FILE
sed -i "s/${DELIM}\(der\|den\) Engel${DELIM}/\1die Helferin\3/g" $FILE
sed -i "s/${DELIM}einen Engel${DELIM}/\1eine Helferin\2/g" $FILE
sed -i "s/${DELIM}des Engels${DELIM}/\1der Helferin\2/g" $FILE
sed -i "s/${DELIM}diesen Engel${DELIM}/\1diese Helferin\2/g" $FILE
sed -i "s/${DELIM}ein Engel${DELIM}/\1eine Helferin\2/g" $FILE
sed -i "s/${DELIM}Engel\(-Registrierung\| wurden\)${DELIM}/\1Helferinnen\2\3/g" $FILE
sed -i "s/${DELIM}Engel${DELIM}/\1Helferin\2/g" $FILE

sed -i "s/${DELIM}hat er${DELIM}/\1hat sie\2/g" $FILE
sed -i "s/${DELIM}seiner${DELIM}/\1ihrer\2/g" $FILE
sed -i "s/${DELIM}sein \(T-Shirt\|Goodie\)${DELIM}/\1ihr \2\3/g" $FILE

grep -Inri "engel" $FILE
grep -Inri "himmel" $FILE

echo "done"
