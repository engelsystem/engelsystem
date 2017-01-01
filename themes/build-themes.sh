#!/bin/bash

FILE_PATH="`dirname \"$0\"`"

for file in `ls "${FILE_PATH}/"*.less`; do
    filename="${file##*/}"
    themeName="${filename%.less}"

    if [[ "$filename" == "base.less" ]]; then
        continue;
    fi

    echo "Building ${themeName}"
    lessc "${file}" > "${FILE_PATH}/../public/css/${themeName}.css"
done
