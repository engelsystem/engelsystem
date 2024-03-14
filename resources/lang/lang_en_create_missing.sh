#!/bin/sh

FOLDER=/data/en_US
FILE=$FOLDER/additional.po

CMD="xgettext --keyword=__ --keyword=_e --keyword=translate --keyword=translatePlural --join-existing --add-location=never --exclude-file=$FOLDER/default.po --output $FILE"

for f in $(find /app -iname "*.php")
do
	$CMD $f
	sed -i "s/charset=CHARSET/charset=UTF-8/" $FILE
done

#for f in $(find /app -iname "*.twig")
#do
#	$CMD --language=python $f
#	sed -i "s/charset=CHARSET/charset=UTF-8/" $FILE
#done

msgen --lang=en_US --output-file=$FILE $FILE
