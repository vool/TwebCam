<?php
/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);
*/

// I can haz configs
$config = json_decode(file_get_contents('config.json'), true);

//set default mode
$mode = "update";

// Check secret
if (isset($_GET['secret']) && $_GET['secret'] == $config['secret']) {

	// require twitter class
	require_once 'vendor/dg/twitter-php/src/twitter.class.php';

	// check mode
	if (isset($_GET['mode'])) {

		$mode = $_GET['mode'];

	}

	switch($mode) {
		case 'update' :
			
			doUpdate();

			break;
			
		case 'check':
			
			doCheck();
			
			break;
	}

} else {
	// no / incorrect secret
	echo "Can't keep a secret ?";

}

/*
 * Update feed with image
 */

function doUpdate() {

	Global $config;

	// loop through accounts
	foreach ($config['account'] as $t) {

		// new twitter
		$twitter = new Twitter($t['keys']['consumer_key'], $t['keys']['consumer_secret'], $t['keys']['access_token'], $t['keys']['access_token_secret']);

		// grab the cam image
		$ch = curl_init($t['webcam_url']);
		$fp = fopen($config['tmp_img'], 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		// process hashtags
		$tags = implode(' ', $t['hashtags']);

		// set tweet bodycontent
		$tweet_text = $t['tweet_text'] . ' ' . $tags;

		try {
			//tweet da tweet
			$tweet = $twitter -> send($tweet_text, $config['tmp_img']);

			// remove the temp image
			unlink($config['tmp_img']);

		} catch (TwitterException $e) {
			echo 'Error: ' . $e -> getMessage();
		}

	}

}


 /*
 * Check status
 */

function doCheck() {
	
	$last_processed_tweets = getTweetStatuses();

	Global $config;

	// loop through accounts
	foreach ($config['account'] as $t) {
		
		echo " Processing ".$t['handle']." <br>";
		
		// new twitter
		$twitter = new Twitter($t['keys']['consumer_key'], $t['keys']['consumer_secret'], $t['keys']['access_token'], $t['keys']['access_token_secret']);

		$opts = array('include_entities' => TRUE);


		if (isset($last_processed_tweets[$t['handle']])) {
			
			echo " Requesting tweets since -".$last_processed_tweets[$t['handle']]." <br>";

			$opts['since_id'] = $last_processed_tweets[$t['handle']];

		}

		$statuses = $twitter -> load(Twitter::REPLIES, null, $opts);

		// reverse so FIFO
		foreach (array_reverse($statuses) as $s) {
			
			echo " Processing $s->id - $s->text <br>";

			if (!empty($s -> entities -> hashtags)) {
				
				foreach ($s->entities->hashtags as $ht) {

					if (strtolower($ht -> text) == 'cheese') {
						echo "#cheese $s->id - $s->text - <br>";
						
						// grab the cam image
						$ch = curl_init($t['webcam_url']);
						$fp = fopen($config['tmp_img'], 'wb');
						curl_setopt($ch, CURLOPT_FILE, $fp);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_exec($ch);
						curl_close($ch);
						fclose($fp);
				
						// process hashtags
						//$tags = implode(' ', $t['hashtags']);
						
						// set the reciepients 
						
						// requesteeee
						$targets = Array('@'.$s->user->screen_name);
						
						// all other users mentioned 
						
						foreach($s->entities->user_mentions as $um){
							
							// don't add yoself
							if(strtolower($um->screen_name) != strtolower($t['handle']) )
							$targets[] = '@'.$um->screen_name;
						}

						// set tweet bodycontent
						//$tweet_text = $t['tweet_text'] . ' ' . $tags;
						$tweet_text = implode(' ', $targets) .' '.$t['tweet_text'];

						try {
							//tweet da tweet
							$tweet = $twitter -> send($tweet_text, $config['tmp_img']);

							echo $tweet_text;

							// remove the temp image
							unlink($config['tmp_img']);

						} catch (TwitterException $e) {
							echo 'Error: ' . $e -> getMessage();
						}

					}

				}

			}

			//update last processed tweet id
			$last_processed_tweets[$t['handle']] = $s->id;

		}

	}

	echo "<hr>";
	print_r($last_processed_tweets);
	
	echo "<hr>";


	// update the last processed tweets status file
	setTweetStatuses($last_processed_tweets);

}


function getTweetStatuses() {
	
	Global $config;
	
	$status_file = $config['status_log'];
	
	//check if file exists
	if(file_exists($status_file)){
		
		$content = unserialize(file_get_contents($status_file));
	
		return $content;
		
	}else{
		
		return false;
		
	}
	

}

function setTweetStatuses($data) {

	Global $config;
	
	$status_file = $config['status_log'];
	
	$content = serialize($data);
	
	$f = fopen($status_file, 'wb');
	
	fwrite($f,$content,strlen($content));
	
	fclose($f);

}
