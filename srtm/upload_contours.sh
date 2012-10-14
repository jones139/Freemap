#!/bin/bash
# Prevents *.contours.sql expanding to '*.contours.sql' if there are no
#  matches - from http://www.cyberciti.biz/faq/bash-loop-over-file/
shopt -s nullglob

psql -d osmgb -f data/contours_init.sql >upload_contours.log 2>&1
for f in data/*.contours.sql
do
    echo processing $f >>upload_contours.log 2>&1
    psql -d osmgb -f $f >>upload_contours.log 2>&1
done
