<?php

$hubspotApiKey = getenv('HUBSPOT_API_KEY');
$thankYouPageUrl = getenv('THANK_YOU_PAGE_URL');
$facebookAppId = getenv('FACEBOOK_APP_ID');
$facebookAppSecret = getenv('FACEBOOK_APP_SECRET');




//determine if file is on http or https
$isSecure = false;
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
    $isSecure = true;
}
elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on') {
    $isSecure = true;
}
$REQUEST_PROTOCOL = $isSecure ? 'https' : 'http';

//determine the url of this file
$urlOfThisFile = $REQUEST_PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . strtok($_SERVER["REQUEST_URI"],'?');




if(!isset($_GET['code'])){
	header ("Location: https://www.facebook.com/dialog/oauth?client_id=$facebookAppId&redirect_uri=$urlOfThisFile&scope=public_profile,email");
	exit;
}




$code = $_GET['code'];

$url = "https://graph.facebook.com/v2.4/oauth/access_token?client_id=$facebookAppId&redirect_uri=$urlOfThisFile&client_secret=$facebookAppSecret&code=$code";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$responseJson = curl_exec($ch);
curl_close($ch);

$responseArray = json_decode($responseJson, true);

$accessToken = $responseArray['access_token'];




$url = "https://graph.facebook.com/v2.4/me?access_token=$accessToken&fields=first_name,last_name,email";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$responseJson = curl_exec($ch);
curl_close($ch);

$responseArray = json_decode($responseJson, true);

$firstName = $responseArray['first_name'];
$lastName = $responseArray['last_name'];
$email = $responseArray['email'];




$propertiesArray = array(
	'properties' => array(
		array(
			'property' => 'email',
			'value' => $email
		),
		array(
			'property' => 'firstname',
			'value' => $firstName
		),
		array(
			'property' => 'lastname',
			'value' => $lastName
		)
	)
);

$propertiesJson = json_encode($propertiesArray);

$url = "https://api.hubapi.com/contacts/v1/contact?hapikey=$hubspotApiKey";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $propertiesJson);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = curl_exec($ch);
curl_close($ch);

header ("Location: $thankYouPageUrl");
exit;

?>