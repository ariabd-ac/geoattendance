<?php
//sessoin_start();
include_once '../sw-library/sw-config.php';
// Include Librari Google Client (API)
include_once '../sw-library/google-client/Google_Client.php';
include_once '../sw-library/google-client/contrib/Google_Oauth2Service.php';

$client_id = '114439569977-atbbh6euifcsehe1rnrcllud9kk3h032.apps.googleusercontent.com'; // Google client ID
$client_secret = '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'; // Google Client Secret
$redirect_url = '' . $site_url . '/oauth/google'; // Callback URL

// Call Google API
$gclient = new Google_Client();
$gclient->setClientId($client_id); // Set dengan Client ID
$gclient->setClientSecret($client_secret); // Set dengan Client Secret
$gclient->setRedirectUri($redirect_url); // Set URL untuk Redirect setelah berhasil login

$google_oauthv2 = new Google_Oauth2Service($gclient);
