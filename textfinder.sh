#!/bin/bash

#
#   This script parses the source of all of the pages found by urlfinder.sh looking for words
#   It then puts all of the words into a single file and uploads it to the server to be added to the database
#

[ $# -ne 0 ] && echo "incorrect amount of args supplied" && exit

# temp file to store urls of individual posts
url_file="/tmp/posts"
# temp file to store the list of words found on each page
word_list="/tmp/word_list"

# for each url
while read url

do 
        # output name of url being indexed
		echo "downloading $url" >&2

        # output url to wordlist file so cs50search_index.php knows what the url is
		echo "$url" > $word_list

        # download url source using curl and write every word found within the content to the word list file
        # the various lines of egrep filter out reddit specific words and phrases which we don't want
		curl "http://www.reddit.com$url" 2>/dev/null\
		| tr -d '\n' \
		| egrep -o 'div class="content".+div class="clearleft"' \
		| egrep -o '>[^<>]+<' \
		| egrep '[ ]' \
		| egrep -v '>[0-9]+ points?<' \
		| egrep -v '>[0-9]+ comments?<' \
		| egrep -v '>[0-9]+ hours?<' \
		| egrep -v '>[0-9]+ months?<' \
		| egrep -v '>[0-9]+ days?<' \
		| egrep -v '>[0-9]+ minutes?<'\
		| egrep -v '>\([0-9]+ child\)<'\
		| egrep -v '>\([0-9]+ children\)<'\
		| egrep -v '>&#32;ago &nbsp;<'\
		| egrep -v '>view the rest of the comments<'\
		| egrep -v '>you are viewing a single comment'"'"'s thread.<'\
		| egrep -o '[a-zA-Z0-9]+' >> $word_list 2>/dev/null

        # update the user on how many words were found
		echo "word list has $(wc -l < $word_list) lines"

        # send the entire word list file for this url to the indexing page to be parsed and logged
		curl -X POST -F "word_list=@$word_list" http://www.sunscripter.com/utilities/cs50search_index.php 2>/dev/null

        # wait so as not to break reddit's 30 requests per minute rule
		sleep 2


done < "$url_file"




