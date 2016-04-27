<?php 
/**
 * users gets redirected here from twitter (if user allowed you app)
 * you can specify this url in https://dev.twitter.com/ and in the previous script
 */ 

//LOADING LIBRARY
require "../vendor/abraham/twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

//TWITTER APP KEYS
$consumer_key = 'yourkey';
$consumer_secret = 'yourkey';

//GETTING ALL THE TOKEN NEEDED
$oauth_verifier = $_GET['oauth_verifier'];
$token_secret = $_COOKIE['token_secret'];
$oauth_token = $_COOKIE['oauth_token'];

//EXCHANGING THE TOKENS FOR OAUTH TOKEN AND TOKEN SECRET
$connection = new TwitterOAuth($consumer_key, $consumer_secret, $oauth_token, $token_secret);
$access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $oauth_verifier));

$accessToken=$access_token['oauth_token'];
$secretToken=$access_token['oauth_token_secret'];

//DISPLAY THE TOKENS
echo "<b>Access Token : </b>".$accessToken."<br />";
echo "<b>Secret Token : </b>".$secretToken."<br />";

 ?>