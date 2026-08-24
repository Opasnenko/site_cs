<?php

$siteUrl = 'https://basebuilder14.site.je/balance_api.php?action=sync&key=mIqQPEjhzwC6_R9GgdYY0QILLwFGcCYQ10fanu39XyE&server_id=3';

$vdsUrl = 'http://46.174.49.52/site-api';

$ch = curl_init($siteUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'BaseBuilder-VDS-Sync/1.0'
]);

$response = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($response === false || $error !== '') {
    die("SITE REQUEST FAILED\n".$error);
}

$data = json_decode($response, true);

if (!is_array($data) || empty($data['ok'])) {
    die(
        "SITE API ERROR\n".
        "HTTP: ".$httpCode."\n".
        "RESPONSE:\n".$response
    );
}

$privileges = $data['privileges'] ?? [];

$json = json_encode(
    $privileges,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);

$ch = curl_init($vdsUrl);

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_USERAGENT => 'BaseBuilder-VDS-Sync/1.0',

    CURLOPT_POSTFIELDS => [
        'action' => 'sync',
        'key' => 'mIqQPEjhzwC6_R9GgdYY0QILLwFGcCYQ10fanu39XyE',
        'privileges' => $json
    ]
]);

$result = curl_exec($ch);
$error = curl_error($ch);
$vdsCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

header('Content-Type: text/plain; charset=utf-8');

echo "SITE HTTP: ".$httpCode."\n";
echo "PRIVILEGES: ".count($privileges)."\n";
echo "VDS HTTP: ".$vdsCode."\n";
echo "CURL ERROR: ".$error."\n";
echo "VDS RESPONSE:\n";
echo $result;