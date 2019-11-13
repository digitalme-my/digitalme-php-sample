<?php
session_start();

$scope = 'openid profile';
$client_id = '<client id>';
$client_secret = '';
$redirect_uri = '<redirect uri>';
$introspect_endpoint = 'https://staging-auth.digitalme.my/oauth2/introspect';
$metadata_url = 'https://staging-auth.digitalme.my/.well-known/openid-configuration';
$metadata = http($metadata_url);

function http($url, $params = false)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    if ($params)
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    return json_decode(curl_exec($ch));
}

if (isset($_GET['logout'])) {
    unset($_SESSION['username']);
    header('Location: /');
    die();
}

if (isset($_SESSION['username'])) {
    echo '<p>Logged in as</p>';
    echo '<p>' . $_SESSION['username'] . '</p>';
    echo '<p><a href="/?logout">Log Out</a></p>';
    die();
}

if (!isset($_SESSION['username'])) {
    $_SESSION['state'] = bin2hex(random_bytes(5));
    $authorize_url = $metadata->authorization_endpoint . '?' . http_build_query([
        'response_type' => 'code',
        'client_id' => $client_id,
        'redirect_uri' => $redirect_uri,
        'state' => $_SESSION['state'],
        'scope' => $scope,
    ]);
    #$authorize_url = 'TODO';
    echo '<p>Not logged in</p>';
    echo '<p><a href="' . $authorize_url . '">Log In</a></p>';
}
