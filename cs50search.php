<?php

    /*
    
        This script serves as both the view and controller for the user controlled search.

        It both renders the form and processes the results retreived from the server
        
    */

?>

<html>

<head>

	<style type="text/css">
		
		body
		{
			font-family:Arial,Helvetica,sans-serif;
		}	

	</style>

	<script type="text/javascript" src="../js/functions.js"></script>

</head>


</body>

<div style="width:100%;text-align:center;">

<div id="box">
</div>

<H4>This is /r/cs50 search</H4> 

<form action="cs50search.php" method="GET">

<input type="text" name = "q" />

<button type="submit">Search</button>

</form>

<?php

    // include files
	require("../includes/functions.php");
	require("../includes/constants.php");

    // if a search request has been submitted
	if ($_SERVER['REQUEST_METHOD'] == 'GET')
	{
            // if we have a search term
			if ( isset($_GET['q'])  )
			{
                // store term
				$q = $_GET['q'];

                // escape query for mysql
				$escaped_q = mysql_escape($q);

                // store ip for stats purposes
				$ip = $_SERVER['REMOTE_ADDR'];

                // escape ip
				$escaped_ip = mysql_escape($ip);

                // store stats
				query("INSERT INTO searches (q, ip) VALUES ('$escaped_q','$escaped_ip')");
				
                // remove any non alpha-numeric chars from query
				$q = preg_replace("/[^a-zA-Z0-9]+/", " ", $q);

                // separate words into an array
				$q = explode(" ", $q);

                // create array for results
				$results = array();
				
                // for each word in query, log which pages it occurs on and how many times it occurs
                foreach ($q as $word)
				{
                    // words to ignore
					$ignore_words = array("and", "the", "you", "on" , "in", "a", "as", "at", "to", "i");
                    
                    // if word is to be ignored
					if(in_array($word, $ignore_words))
					{
                        // skip this word
						continue;
					}
                    
                    // escape word for mysql
					$word = mysql_escape($word);

                    // get existing instances of this word from DB
					$rows = query("SELECT * FROM `cs50_search` WHERE `word` REGEXP '^$word\n?$' ORDER BY `count` DESC");

					// foreach row, if in results, add count to count, else add to results
					foreach($rows as $row)
					{
						$updated_existing = false;

						foreach($results as $key => $result)
						{
							if ($result['url'] == $row['url'])
							{
								$results[$key]['count'] += $row['count'];
								$updated_existing = true;

							}
						}
						
						if ($updated_existing == false)
						{
							// add row to results
							array_push($results, $row);
						}
					}

				}


				// sort the words so the ones with highest count appear at the top
				function sort_array_by_count($arr)
				{

					$changes = 0;

					for($i = 0; $i < count($arr) - 1; $i++)
					{
						$temp_row = array();
                        
                        // if this word has a lower count, move it down the list
						if ($arr[$i]['count'] < $arr[$i + 1]['count']) 
						{
							$temp_row = $arr[$i];
							$arr[$i] = $arr[$i + 1];
							$arr[$i + 1] = $temp_row;
							$changes++;
						}
					}

					if($changes > 0)
					{
						$arr = sort_array_by_count($arr);
					}

					return $arr;
					
				}

				$results = sort_array_by_count($results);

				if(count($results) == 0)
				{
					echo "<H4>Sorry, no results for '{$_GET['q']}'</H4>";
				}
				else
				{
					echo "<H4>Results (sorted by relevance):</H4>";
				}

				$i = 0;

                // show each result as title with link and then go and fetch the snippets
				foreach($results as $row)
				{
					$url = $row['url'];
					$encoded_url = urlencode($row['url']);

					$title = query_single("SELECT * FROM cs50_search_page_title WHERE url = '{$row['url']}'", "title");
					echo "<a href=\"http://www.reddit.com{$row['url']}\">$title</a></br>";
					foreach ($q as $word)
					{
						echo "<div id=\"box$i\"></div>";
						echo "<script type=\"text/javascript\">";
						echo "ajax(\"box$i\", \"cs50search_snippets.php?word=$word&url=$encoded_url\");";
						echo "</script>";
						$i++;
					}


				}	


			}
	}


?>
<br />
<br />
<span style="font-size:0.8em">This is a work in progress. Please send any comments/suggestions to <a href="http://www.reddit.com/user/sundayscripter">reddit.com/u/sundayscripter.</a></span>

</div>

</body>
</html>
