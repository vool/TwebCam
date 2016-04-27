<?php

exit;

/*
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(-1);*/



//LOADING LIBRARY
require "../vendor/abraham/twitteroauth/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

// I can haz configs
$config = json_decode(file_get_contents('../config.json'), true);

//CONNECTION TO THE TWITTER APP TO ASK FOR A REQUEST TOKEN
$connection = new TwitterOAuth($config['keys']['consumer_key'], $config['keys']['consumer_secret']);
$request_token = $connection->oauth("oauth/request_token", array("oauth_callback" => "http://lab.vool.ie/twebcam/oauth/callback.php"));
//callback is set to where the rest of the script is

//TAKING THE OAUTH TOKEN AND THE TOKEN SECRET AND PUTTING THEM IN COOKIES (NEEDED IN THE NEXT SCRIPT)
$oauth_token=$request_token['oauth_token'];
$token_secret=$request_token['oauth_token_secret'];
setcookie("token_secret", " ", time()-3600);
setcookie("token_secret", $token_secret, time()+60*10);
setcookie("oauth_token", " ", time()-3600);
setcookie("oauth_token", $oauth_token, time()+60*10);

//GETTING THE URL FOR ASKING TWITTER TO AUTHORIZE THE APP WITH THE OAUTH TOKEN
$url = $connection->url("oauth/authorize", array("oauth_token" => $oauth_token));

//REDIRECTING TO THE URL
header('Location: ' . $url);

?>