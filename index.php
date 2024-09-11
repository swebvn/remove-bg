<?php

error_reporting(E_ALL);

include 'vendor/autoload.php';

// This app parse the url with format
// http://domain.com/otherdomain.com/xxx/image.png
// then extract the remote image https://otherdomain.com/xxx/image.png
// and display it on the browser
// parse the url

$url = $_SERVER['REQUEST_URI'];
$parts = explode('/', $url);
// $remoteUrl should be remaining parts of the url
$remoteUrl = 'http://' . implode('/', array_slice($parts, 1));
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
$imageContent = file_get_contents($remoteUrl, false, stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ],
]));

// store the image in folder /tmp
// store in current folder /tmp folder
$filename = __DIR__ . '/tmp/' . uniqid() . '.' . $ext;
file_put_contents($filename, $imageContent);

// remove the background
$action = new \Swebvn\RemoveBg\RemoveBackground();
$content = $action->handle($filename);

header('Content-Type: image/png');
echo $content;

// remove the image
@unlink($filename);