<?php 

    /*
    /    This script reads from the uploaded word list and inserts each word into a mysql
    /    database alongside the url where the word was found   
    */



    // include necessary files
	require("../includes/functions.php");
	require("../includes/constants.php");

    
    // check word list has been uploaded
    if(!isset($_FILES['word_list']))
    {
        // if its not there, complain and exit
        die("Word list file is absent");
    }

    // file pointer to the word list file
	$fp = fopen($_FILES['word_list']['tmp_name'], 'rb');

    // iterator
	$i = 0;

    // counter for words added
	$words_added = 0;

    // read file line by line
    while ( ($line = fgets($fp)) !== false) 
	{
        
        // if its the first line, it should be the url so process it as such
		if ( $i == 0 )
		{

            // get url title
			$page_title = get_url_title("http://www.reddit.com$line");
            
            // print title to user
			echo "title = $page_title";

            // escape the url for mysql
			$url = mysql_escape($line);

            // escape the title
			$page_title = mysql_escape($page_title);

            // remove previous entries for this post
			query("DELETE FROM cs50_search WHERE url = '$url'");

            // remove page title entry for this post
			query("DELETE FROM cs50_search_page_title WHERE url = '$url'");

			// add page title to db
			query("INSERT INTO cs50_search_page_title (url, title) values ('$url','$page_title')");
			

		}
		else // else line is a word so add to database
		{
			// remove the newline char
			$word = preg_replace("#\n#", "", $line);
			
            // convert to lower case
            $word = strtolower($word);

            // escape for mysql
			$word = mysql_escape($word);

            // if this word/url combo does not exist already, add it in
			if (gettype(query("SELECT * FROM cs50_search WHERE url = '$url' AND word = '$word'")) != "array")
			{
                // add word to db
				query("INSERT INTO cs50_search (url,word,count) VALUES ('$url','$word', 1)");
				
                // increment counter
                $words_added++;
			}	
			else // if it does exist, update the count
			{
                // update count for word/url combo
				query("UPDATE cs50_search SET count=(count + 1) WHERE url = '$url' AND word = '$word'");
				
                // increment counter
                $words_added++;
			}	
		}	

        // increment line number iterator
		$i++;

    }

    // print the total number of words added
	echo PHP_EOL."$words_added words added.".PHP_EOL;

?>

