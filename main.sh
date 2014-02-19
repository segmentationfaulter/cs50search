#!/bin/bash

#
#    This script continually indexes the /r/cs50 subreddit until it is stopped
#

# if any args are supplied, the usage is wrong so complain and exit
[ $# -ne 0 ] && echo "incorrect amount of args supplied" && exit

# run this program repeatedly
while true

do

# find the urls we want to index
./urlfinder.sh

# search those urls for indexable words and index them
./textfinder.sh

done
