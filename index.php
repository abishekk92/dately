<?php

// Enforce https on production
if ($_SERVER['HTTP_X_FORWARDED_PROTO'] == "http" && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
  exit();
}

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to Facebook specific utilities defined in 'FBUtils.php'
require_once('FBUtils.php');
// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');
// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');

/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

// Log the user in, and get their access token
$token = FBUtils::login(AppInfo::getHome());
if ($token) {

  // Fetch the viewer's basic information, using the token just provided
  $basic = FBUtils::fetchFromFBGraph("me?access_token=$token");
  $basic_j=json_decode($basic);
  //$my_id = assertNumeric(idx($basic, 'id'));

  // Fetch the basic info of the app that they are using
  $app_id = AppInfo::appID();
  $app_info = FBUtils::fetchFromFBGraph("$app_id?access_token=$token");

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = array_values(
    idx(FBUtils::fetchFromFBGraph("me/likes?access_token=$token&limit=4"), 'data', null, false)
  );

  // This fetches 4 of your friends.
  $friends = array_values(
    idx(FBUtils::fetchFromFBGraph("me/friends?access_token=$token&limit=4"), 'data', null, false)
  );

  // And this returns 16 of your photos.
  $photos = array_values(
    idx($raw = FBUtils::fetchFromFBGraph("me/photos?access_token=$token&limit=16"), 'data', null, false)
  );


  // This formats our home URL so that we can pass it as a web request
  $encoded_home = urlencode(AppInfo::getHome());
  $redirect_url = $encoded_home . 'close.php';

  // These two URL's are links to dialogs that you will be able to use to share
  // your app with others.  Look under the documentation for dialogs at
  // developers.facebook.com for more information
  $send_url = "https://www.facebook.com/dialog/send?redirect_uri=$redirect_url&display=popup&app_id=$app_id&link=$encoded_home";
  $post_to_wall_url = "https://www.facebook.com/dialog/feed?redirect_uri=$redirect_url&display=popup&app_id=$app_id";
} else {
  // Stop running if we did not get a valid response from logging in
  exit("Invalid credentials");
}
$template['male']=<<<EOT
 <!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->

<head>
<meta charset="utf-8" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<title>WHERE THE FUCK SHOULD I GO FOR DATE?</title>
<meta property="og:title" content="WHERE THE FUCK SHOULD I GO FOR DATE?"/>
<meta property="og:type" content="website"/>
<meta property="og:url" content="http://wherethefuckshouldigoforadate.herokuapp.com"/>
<meta property="og:image" content="http://wherethefuckshouldigoforadate.com/apple-touch-icon-114x114-precomposed.png"/>
<meta name="og:site_name" content="WTFSIGFD"/>
<meta name="og:description" content="WHERE THE FUCK SHOULD I GO FOR DATE"/>
<meta name="description" content="WHERE THE FUCK SHOULD I GO FOR Date"/>
<meta name="keywords" content="where the fuck should i go for a date? dates, recommendations, pubs, bars, maps, directions, google, api, html5, css3, jquery, responsive, cool, coolography, "/>
<meta name="author" content="Abishek"/>
<meta property="fb:app_id" content="186690998096099"/>

<!-- Set the viewport width to device width for mobile -->
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<!-- Included CSS Files -->
<link rel="stylesheet" href="stylesheets/foundation.css"/>
<link rel="stylesheet" href="stylesheets/app.css"/>
<!-- IE Fix for HTML5 Tags -->
<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="js/modernizr-2.0.min.js"></script>
</head>
<body>
<!-- container -->
<div class="container"> <a  id="wait">Hang on <strong>little</strong> fella! <span></span></a> 
    <!-- GEOLOCATION-->
    <div id="locate" class="row">
        <div class="twelve columns">
            <form id="locationBar" action="javascript:codeAddress()">
                <div id="error">can't find your fucking location. try again</div>
                <input id="location" placeholder="Where are you bro?" type="text">
                <input type="submit" class="submit" value="Find me a place to take that bitch">
            </form>
        </div>
    </div>
    <!--RECOMMENDATION-->
    <div id="recommendation" class="row">
        <div class="twelve columns"> <span id="destination"></span>
            <div id="mapcontainer">
                <div id="address"></div>
                <div id="map"></div>
            </div>
        </div>
    </div>
    <!--ACTIONS-->
    <div id="actions" class="row">
        <div class="six columns"> <a href="?wherethefuck" id="wrong" title="Fuck,Anna Wi-Fi">Err..That's not where I'm</a> </div>
        <div class="six columns"> <a href="#" id="shit" title="Gotchya.Lemme see le book">Naah,This place is gunna burn a hole on my wallet</a> </div>
    </div>
<!-- MAPS --> 
<script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places,adsense&sensor=true&key=AIzaSyCTxiWhuS0yb-zBc11xjfNlFubjdYkZtp8" type="text/javascript"></script> 

<!-- Included JS Files --> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> 
<script src="js/script.js"></script>
</body>
</html>
EOT;

$template['female']=<<<EOT

<!DOCTYPE html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html lang="en"> <!--<![endif]-->

<head>
<meta charset="utf-8" />
<meta name="apple-mobile-web-app-capable" content="yes" />
<title>Where do I go for a Date?</title>
<meta property="og:title" content="WHERE THE FUCK SHOULD I GO FOR DATE?"/>
<meta property="og:type" content="website"/>
<meta property="og:url" content="http://wherethefuckshouldigoforadate.herokuapp.com"/>
<meta property="og:image" content="http://wherethefuckshouldigoforadate.com/apple-touch-icon-114x114-precomposed.png"/>
<meta name="og:site_name" content="WTFSIGFD"/>
<meta name="og:description" content="WHERE THE FUCK SHOULD I GO FOR DATE"/>
<meta name="description" content="WHERE THE FUCK SHOULD I GO FOR Date"/>
<meta name="keywords" content="where the fuck should i go for a date? dates, recommendations, pubs, bars, maps, directions, google, api, html5, css3, jquery, responsive, cool, coolography, "/>
<meta name="author" content="coolography"/>
<meta property="fb:app_id" content="186690998096099"/>
<!-- Set the viewport width to device width for mobile -->
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<!-- Included CSS Files -->
<link rel="stylesheet" href="stylesheets/foundation.css"/>
<link rel="stylesheet" href="stylesheets/app.css"/>
<!-- IE Fix for HTML5 Tags -->
<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="js/modernizr-2.0.min.js"></script>
</head>
<body>
<!-- container -->
<div class="container"> <a  id="wait">Hang on <strong>baby</strong> doll! <span></span></a> 
    <!-- GEOLOCATION-->
    <div id="locate" class="row">
        <div class="twelve columns">
            <form id="locationBar" action="javascript:codeAddress()">
                <div id="error">can't find your fucking location. try again</div>
                <input id="location" placeholder="Where are you hon?" type="text">
                <input type="submit" class="submit" value="Where do I take him?">
            </form>
        </div>
    </div>
    <!--RECOMMENDATION-->
    <div id="recommendation" class="row">
        <div class="twelve columns"> <span id="destination"></span>
            <div id="mapcontainer">
                <div id="address"></div>
                <div id="map"></div>
            </div>
        </div>
    </div>
    <!--ACTIONS-->
    <div id="actions" class="row">
        <div class="six columns"> <a href="?wherethefuck" id="wrong" title="Fuck,Anna Wi-Fi">I'm not here</a> </div>
        <div class="six columns"> <a href="#" id="shit" title="Gotchya.Lemme see le book">Show me,much more romantic place.Pretty Please?</a> </div>
    </div>
<!-- MAPS --> 
<script src="https://maps.googleapis.com/maps/api/js?v=3&libraries=places,adsense&sensor=true&key=AIzaSyCTxiWhuS0yb-zBc11xjfNlFubjdYkZtp8" type="text/javascript"></script> 

<!-- Included JS Files --> 
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script> 
<script src="js/script.js"></script>
</body>
</html>
EOT;
 
if($basic_j->gender=='male')
{
	echo $template['male'];
}
else
{
	echo $template['female'];
} 
?>

