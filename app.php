<?php

// require config
$config = json_decode(file_get_contents('config.json'), true);

// require twitter class 
require_once 'vendor/dg/twitter-php/src/twitter.class.php';

// settings
$tmp_img = $config['tmp_img'];


foreach ($config['account'] as $t) {

	// new twitter
	$twitter = new Twitter($t['keys']['consumer_key'], $t['keys']['consumer_secret'], $t['keys']['access_token'], $t['keys']['access_token_secret']);

	// grab the cam image
	$ch = curl_init($t['webcam_url']);
	$fp = fopen($tmp_img, 'wb');
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_exec($ch);
	curl_close($ch);
	fclose($fp);
	
	// hashtags
	$tags = implode( ' ', $t['hashtags'] );
	
	$tweet_text = $t['tweet_text']. ' '.$tags;
	

try {
		//tweet tweet
		$tweet = $twitter -> send($tweet_text, $tmp_img);

		// remove the cam image
		unlink($tmp_img);

	} catch (TwitterException $e) {
		echo 'Error: ' . $e -> getMessage();
	}

}
