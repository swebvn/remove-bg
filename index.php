<?php

error_reporting(-1);

include 'vendor/autoload.php';

// This app parse the url with format
// http://domain.com/otherdomain.com/xxx/image.png
// then extract the remote image https://otherdomain.com/xxx/image.png
// and display it on the browser
// parse the url

$url = $_SERVER['REQUEST_URI'];
$parts = parse_url($url);
// $remoteUrl should be remaining parts of the url
$remoteUrl = 'https:/' . $parts['path'];
if ($parts['query']) {
    $remoteUrl .= '?' . $parts['query'];
}
// validate the url
if (filter_var($remoteUrl, FILTER_VALIDATE_URL) === false) {
    echo 'Invalid URL';
    exit;
}

// get the extension of image
$ext = pathinfo($remoteUrl, PATHINFO_EXTENSION);
// only accept png, jpg, jpeg
if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
    echo 'Invalid image format';
    exit;
}

// get the image
$client = new \GuzzleHttp\Client([
    'verify' => false,
    'headers' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3',
    ],
]);
try {
    $response = $client->get($remoteUrl);
} catch (\GuzzleHttp\Exception\GuzzleException $e) {
    echo 'Error: ' . $e->getMessage();
    exit;
}
$imageContent = $response->getBody()->getContents();

// store the image in folder /tmp
// store in current folder /tmp folder
$filename = __DIR__ . '/tmp/' . uniqid() . '.' . $ext;
file_put_contents($filename, $imageContent);

// remove the background
$action = new \Swebvn\RemoveBg\RemoveBackground();
try {
    $content = $action->handle($filename);
} catch (\Exception $e) {
    echo 'Error when remove background: ' . $e->getMessage();
    exit;
}

header('Content-Type: image/png');
echo $content;

// remove the image
@unlink($filename);