<?php
session_start();

$scope = 'openid profile';
$client_id = '<client id>';
$client_secret = '';
$redirect_uri = '<redirect uri>';
$introspect_endpoint = 'https://staging-auth.digitalme.my/oauth2/introspect';
$metadata_url = 'https://staging-auth.digitalme.my/.well-known/openid-configuration';
$metadata = http($metadata_url);

function http($url, $params = false, $header = false)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($params) curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    if ($header) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    return json_decode(curl_exec($ch));
}

if (isset($_GET['code'])) {
    if ($_SESSION['state'] != $_GET['state']) {
        die('Authorization server returned an invalid state parameter');
    }
    if (isset($_GET['error'])) {
        die('Authorization server returned an error: ' . htmlspecialchars($_GET['error']));
    }
    $response = http($metadata->token_endpoint, [
        'grant_type' => 'authorization_code',
        'code' => $_GET['code'],
        'redirect_uri' => $redirect_uri,
        'client_id' => $client_id,
        'client_secret' => $client_secret,
    ]);
    if (!isset($response->access_token)) {
        die('Error fetching access token');
    }

    echo 'Access Token: ' . $response->access_token;
    echo '<br>';

    // -- userinfo
    $userinfo = http($metadata->userinfo_endpoint, false, [
        'Accept: application/json',
        'Authorization: Bearer ' . $response->access_token
    ]);
    print_r($userinfo);
    echo '<br>';

    // -- introspec
    $introspect = http($introspect_endpoint, [
        'token' => $response->access_token,
        'scope' => $scope,
    ]);
    print_r($introspect);
    echo '<br>';
}