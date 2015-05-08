<?php

// I can haz configs
$config = json_decode(file_get_contents('config.json'), true);

// Check secret
if (isset($_GET['secret']) && $_GET['secret'] == $config['secret']) {
	
	// require twitter class 
	require_once 'vendor/dg/twitter-php/src/twitter.class.php';

	// temp image file
	$tmp_img = $config['tmp_img'];

	// loop through accounts
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
	
		// process hashtags
		$tags = implode( ' ', $t['hashtags'] );
	
		// set tweet bodycontent
		$tweet_text = $t['tweet_text']. ' '.$tags;
	

	try {
			//tweet da tweet
			$tweet = $twitter -> send($tweet_text, $tmp_img);

			// remove the temp image
			unlink($tmp_img);

		} catch (TwitterException $e) {
			echo 'Error: ' . $e -> getMessage();
		}

	}

} else {
	// no / incorrect secret
	echo "Can't keep a secret ?";

}
