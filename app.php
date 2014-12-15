<?php

// require config
require_once('config.php');

// require twitter class 
require_once 'vendor/dg/twitter-php/src/twitter.class.php';

// new twitter
$twitter = new Twitter(CONSUMER_KEY, CONSUMER_SECRET, ACCESS_TOKEN, ACCESS_TOKEN_SECRET);

	// grab the cam image 
	$ch = curl_init(WEBCAM_URL);
	$fp = fopen(TMP_IMG, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);

try {
	//tweet tweet
	$tweet = $twitter -> send(TWEET_TEXT, TMP_IMG);
	
	// remove the cam image 
	unlink(TMP_IMG);
	
} catch (TwitterException $e) {
	echo 'Error: ' . $e -> getMessage();
}

