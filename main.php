#! /usr/bin/php
<?php
/* PHP Markov_ebooks twitter engine
/*
/* This takes a twitter user and
/* gets their most recent tweets,
/* uses those tweets in a markov
/* chain generator, then post the
/* result to twitter in a _ebooks 
/* account. If you don't get this,
/* you aren't elite. 
/*
*/

/* configuration for this script */
$base_tweetist ='bennyfactor'; //twitter name goes here, e.g. josecanseco
$cache_location = './'.$base_tweetist.'.txt'; 
$tweet_length = 100; // remember max 140 chars
$randomness = 4; //(1-5, 1 just prints random characters, 5 is hardly a markov chain
$hashtag ='#drunj'; // add a hashtag here if you want it to show up occasionally
$hash_frequency = 3; //frequency of hashtag 0-10, 10 means always








/*Nothing to edit (from a user perspective) below */









/**
 * TWITTER FEED PARSER
 * 
 * @version	1.1.3
 * @author	Jonathan Nicol
 * @link	http://f6design.com/journal/2010/10/07/display-recent-twitter-tweets-using-php/
 * 
 * Notes:
 * Caching is employed because Twitter only allows their RSS feeds to be accesssed 150
 * times an hour per user client.
 * --
 * Dates can be displayed in Twitter style (e.g. "1 hour ago") by setting the 
 * $twitter_style_dates param to true.
 * 
 * Credits:
 * Hashtag/username parsing based on: http://snipplr.com/view/16221/get-twitter-tweets/
 * Feed caching: http://www.addedbytes.com/articles/caching-output-in-php/
 * Feed parsing: http://boagworld.com/forum/comments.php?DiscussionID=4639
 * Modified for Markov_ebooks by Ben Lamb
 */
 
function display_latest_tweets(
	$twitter_user_id,
	$cache_file,
	$tweets_to_display = 500,
	$ignore_replies = true,
	$twitter_wrap_open = '',
	$twitter_wrap_close = '',
	$tweet_wrap_open = '',
	$meta_wrap_open = '',
	$meta_wrap_close = '',
	$tweet_wrap_close = '',
	$date_format = 'g:i A M jS',
	$twitter_style_dates = false
	){
	// Seconds to cache feed (1 hour).
	$cachetime = 60*60;
	// Time that the cache was last filled.
	$cache_file_created = ((file_exists($cache_file))) ? filemtime($cache_file) : 0;
 
	// A flag so we know if the feed was successfully parsed.
	$tweet_found = false;
 
	// Show file from cache if still valid.
	if (time() - $cachetime < $cache_file_created) {
 
		$tweet_found = true;
		// Display tweets from the cache.
		//readfile($cache_file);
		$twitter_html = file_get_contents($cache_file);	
	} else {
 
		// Cache file not found, or old. Fetch the RSS feed from Twitter.
		$rss = file_get_contents('http://twitter.com/statuses/user_timeline/'.$twitter_user_id.'.rss');
 
		if($rss) {
 
			// Parse the RSS feed to an XML object.
			$xml = simplexml_load_string($rss);
 
			if($xml !== false) {
 
				// Error check: Make sure there is at least one item.
				if (count($xml->channel->item)) {
 
					$tweet_count = 0;

 
					// Open the twitter wrapping element.
					$twitter_html = $twitter_wrap_open;
 
					// Iterate over tweets.
					foreach($xml->channel->item as $tweet) {
 
						// Twitter feeds begin with the username, "e.g. User name: Blah"
						// so we need to strip that from the front of our tweet.
						$tweet_desc = substr($tweet->description,strpos($tweet->description,":")+2);
						$tweet_desc = htmlspecialchars($tweet_desc);
						$tweet_first_char = substr($tweet_desc,0,1);
 
						// If we are not ignoring replies, or tweet is not a reply, process it.
						if ($tweet_first_char!='@' || $ignore_replies==false){
 
							$tweet_found = true;
							$tweet_count++;
 
							// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
							/*$tweet_desc = preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="$1">$1</a>',$tweet_desc);
							*/$tweet_desc = preg_replace('/(https?:\/\/[^\s*<>]+)/','',$tweet_desc); //don't print URLs
							/*$tweet_desc = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2">@$2</a>', $tweet_desc);
							*/$tweet_desc = preg_replace('/(^|[\n\s])@([^\s*\t\n\r<:]*)/is', '$1$2', $tweet_desc);
							/*$tweet_desc = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2">#$2</a>', $tweet_desc);
 							
 							// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
							$tweet_time = strtotime($tweet->pubDate);	
 							if ($twitter_style_dates){
								// Current UNIX timestamp.
								$current_time = time();
								$time_diff = abs($current_time - $tweet_time);
								switch ($time_diff) 
								{
									case ($time_diff < 60):
										$display_time = $time_diff.' seconds ago';                  
										break;      
									case ($time_diff >= 60 && $time_diff < 3600):
										$min = floor($time_diff/60);
										$display_time = $min.' minutes ago';                  
										break;      
									case ($time_diff >= 3600 && $time_diff < 86400):
										$hour = floor($time_diff/3600);
										$display_time = 'about '.$hour.' hour';
										if ($hour > 1){ $display_time .= 's'; }
										$display_time .= ' ago';
										break;          
									default:
										$display_time = date($date_format,$tweet_time);
										break;
								}
 							} else {
 								$display_time = date($date_format,$tweet_time);
 							}
 							*/ //don't bother with this stuff either
							// Render the tweet.
							/*$twitter_html .= $tweet_wrap_open.html_entity_decode($tweet_desc).$meta_wrap_open.'<a href="http://twitter.com/'.$twitter_user_id.'">'.$display_time.'</a>'.$meta_wrap_close.$tweet_wrap_close;*/
							$twitter_html .= $tweet_wrap_open.html_entity_decode($tweet_desc).' '.$meta_wrap_open.$meta_wrap_close.$tweet_wrap_close;
						}
 
						// If we have processed enough tweets, stop.
						if ($tweet_count >= $tweets_to_display){
							break;
						}
 
					}
 
					// Close the twitter wrapping element.
					$twitter_html .= $twitter_wrap_close;

 
					// Generate a new cache file.
					$file = fopen($cache_file, 'w');
 
					// Save the contents of output buffer to the file, and flush the buffer. 
					fwrite($file, $twitter_html); //get rid of output buffering nonsense it's all in twitter_html
					fclose($file); 

 
				}
			}
		}
	} 
	// In case the RSS feed did not parse or load correctly, show a link to the Twitter account.
	if (!$tweet_found){
		echo $twitter_wrap_open.$tweet_wrap_open.'Oops, our twitter feed is unavailable right now. '.$meta_wrap_open.'<a href="http://twitter.com/'.$twitter_user_id.'">Follow us on Twitter</a>'.$meta_wrap_close.$tweet_wrap_close.$twitter_wrap_close;
	}
return $twitter_html;
}
?>

<?php  
/* get the info from the twitter feed parser in order to send it to the markov chainer */
$string = display_latest_tweets($base_tweetist, $cache_location);

/*below section based on http://www.decontextualize.com/teaching/ppp/php-classes-web-templates/ */

$mark = new Markov($randomness, $tweet_length);

$mark->feed($string);

  echo $mark->gen();
  if ( $hash_frequency <= rand(0,10) ) {
  echo ' '.$hashtag; }


function choice($in) {
  return $in[rand(0, count($in) - 1)];
}

class Markov {
  protected $n;
  protected $max;
  protected $grams = array();
  protected $begin = array();

  public function __construct($n, $max) {
    $this->n = $n;
    $this->max = $max;
  }

  public function feed($line) {

    // add the beginning of this string to the $begin array
    $this->begin[] = substr($line, 0, $this->n);

    // create $grams array by grabbing strings of length $n from
    // the given string
    $i = 0;
    while ($i < strlen($line) - 1) {
      $gram = substr($line, $i, $this->n);
      $following = substr($line, $i + $this->n, 1);
      if (array_key_exists($gram, $this->grams)) {
        $this->grams[$gram][] = $following;
      }
      else {
        $this->grams[$gram] = array($following);
      }
      $i++;
    }

  }

  public function gen($startwith="") {

    $output = "";
    $current = "";
    if ($startwith) {
      $current = $startwith;
    }
    else {
      $current = choice($this->begin);
    }
    $output .= $current;
    $max = $this->max;

    while ($max--) {
      if ($current && array_key_exists($current, $this->grams)) {
        $possible = $this->grams[$current];
        $next = choice($possible);
        $output .= $next;
        $current = substr($current . $next, 1, $this->n);
      }
      else {
        break;
      }
    }

    return $output;

  }

  public function get_grams() {
    return $this->grams;
  }

}

/* end markov chainer */
?>