<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<script src="js/jquery.js"></script>
		<script src="js/jquery.progressbar.min.js"></script>
	</head>
	<body>
<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
include 'CurlAxel.php';
//$fileurl = "http://cachefly.cachefly.net/100mb.test";
$fileurl = "http://93.190.137.8/1000mb.bin";

echo "downloading $fileurl <br>";
$curlaxel = new CurlAxel;
$curlaxel->setUrl($fileurl);  
$curlaxel->setProgressCallback(); 
$curlaxel->setBufferSize(32*1024*1024);
$curlaxel->activeLog(true); 
$curlaxel->download();
