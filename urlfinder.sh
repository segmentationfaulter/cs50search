#!/bin/bash

#
#   This script searches /r/cs50 for posts and writes there urls to the url_file to be used by the parsing script, textfinder.sh 
#

[ $# -ne 0 ] && echo "incorrect amount of args supplied" && exit

#store temp file locations
url_file="/tmp/urls"
posts_file="/tmp/posts"

# urls to search for posts
echo "/r/cs50/" > "$url_file"
echo "/r/cs50/new" >> "$url_file"

# for each url found
while read url

do

	echo "$0 curling $url"

    # get source of url and remove all newline chars
	html=$(curl "http://www.reddit.com$url"	2>/dev/null | tr -d '\n') 

    # reddit does not allow more than 30 requests per minute from a robot, so sleep for 2 seconds
	sleep 2
	
    # search the source for post urls
	echo "$html" | egrep -o '/r/cs50/comments/[a-zA-Z0-9%_-]+/[a-zA-Z0-9%_-]+' >> "$posts_file"

    # search the source for the link to the next results page
	echo "$html" | egrep -o '/r/cs50/\?[0-9a-zA-Z&=;]+after=[a-zA-Z0-9_]+' | awk '{gsub(/&amp;/,"\\&");print}' >> "$url_file" || echo "couldn't find a link"

    # remove duplicates from posts file
	sort -u "$posts_file" -o "$posts_file"

done < "$url_file"
