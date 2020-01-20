<?php // word.php

require_once 'init.php';
require 'refine_page.php';

use DOMDocument;
use DOMXpath;

/**
 * Return CUrl options
 */
function fiGetCurlOpt() {
	// echo 'Ok';
	$headers = array(
		"User-Agent: curl",
		"Accept:text/plain, text/html, */*",
		"Accept-Language:en-US;q=0.5,en;q=0.3,zh-CN,zh;q=0.8",
		"Accept-Encoding:deflate",
		"Connection:close"
	);

	$defaults = array(
		CURLOPT_HEADER => 1, // Doesn't include header in output
		// CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_FORBID_REUSE => 1,
		CURLOPT_TIMEOUT => 10,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_HTTPHEADER => $headers,
		CURLOPT_COOKIEFILE => 'tmp/cookies.txt',
		CURLOPT_COOKIEJAR => 'tmp/cookies.txt',
	);

	return $defaults;
}

/**
 * Get verification picture.
 */
function fiGetVerify($curlOpts)
{
	$url = 'https://investorservice.cfmmc.com/';
	$curlOpts[CURLOPT_URL] = $url;

	$ch = curl_init();
	curl_setopt_array($ch, $curlOpts);

	if( ! $content = curl_exec($ch))
	{
		trigger_error(curl_error($ch));
	}

	$header  = curl_getinfo($ch);
	

	$header_content = substr($content, 0, $header['header_size']);
	$body_content = trim(str_replace($header_content, '', $content));
	$pattern = "#Set-Cookie:\\s+(?<cookie>[^=]+=[^;]+)#m"; 
	preg_match_all($pattern, $header_content, $matches); 
	$cookies = implode("; ", $matches['cookie']);

	// Page has an encoding mismatch error.
	$body_content = str_replace('charset=gb2312', 'charset=utf8', $body_content);
	// file_put_contents('tmp.html', $body_content);

	// Parse page
	$doc = new DomDocument();
	if (mb_detect_encoding($body_content, 'UTF-8', true)) {
		// echo $body_content;
		$ret = @$doc->loadHtml('<?xml encoding="utf-8" ?>'.$body_content);
		if ($ret) {

			$xpath = new DOMXpath($doc);
			$eles = $xpath->query('/html/body/form/div/input');
			if (count($eles)) {
				$input = $eles[0];
				$inputName = $input->attributes->getNamedItem('name')->nodeValue;
				$inputValue = $input->attributes->getNamedItem('value')->nodeValue;
				setcookie('inputName', $inputName);
				setcookie('inputValue', $inputValue);
			}

			$eles = $xpath->query('//*[@id="imgVeriCode"]');
			if (count($eles)) {
				$img = $eles[0];
				$imgSrc = $img->attributes->getNamedItem('src')->nodeValue;

				// Image Url
				$imgVerifyUrl = 'https://investorservice.cfmmc.com'.$imgSrc;
				echo $imgVerifyUrl.'<br/>';
				$fileName = 'tmp/verify.jpg';
				@unlink($fileName);

				$curlOpts[CURLOPT_URL] = $imgVerifyUrl;
				$curlOpts[CURLOPT_HEADER] = 0;
				curl_setopt_array($ch, $curlOpts);
				$imgContent = file_get_contents($imgVerifyUrl, false);

				if(!$imgContent = curl_exec($ch))
				{
					trigger_error(curl_error($ch));
				}

				file_put_contents($fileName, $imgContent);
				while (!file_exists($fileName))
					sleep(1);

				echo 'got_verify_pic';
			}

		} else {
			echo "Can't parse html";
		}
		
	} else {
		echo 'Html encoding is not utf8';
	}

	curl_close($ch);
}

/**
 * Login with inputed verification code
 */
function fiLogin($curlOpts, $account, $password, $veriCode) {

	$url = 'https://investorservice.cfmmc.com/login.do';
	$curlOpts[CURLOPT_URL] = $url;
	$curlOpts[CURLOPT_POST] = true;

	$postFields = array(
		'showSaveCookies' => '',
		'userID' => $account,
		'password' => $password,
		'vericode' =>  $veriCode,
	);

	if (isset($_COOKIE['inputName']) && (isset($_COOKIE['inputValue']))) {
		$postFields[$_COOKIE['inputName']] = $_COOKIE['inputValue'];
	}

	$curlOpts[CURLOPT_POSTFIELDS] = $postFields;

	$ch = curl_init();
	curl_setopt_array($ch, $curlOpts);

	if( ! $content = curl_exec($ch))
	{
		trigger_error(curl_error($ch));
	}

	// Create per account folder
	if (!is_dir("tmp/$account"))
		mkdir("tmp/$account");

	echo $content;
	curl_close($ch);
}

/**
 * Return a array contains all day up to now
 */
function yearDateArray($year) {

    $dates = [];

    $date = new DateTime();
    $date->setDate($year, '1', '1');

	// Push first day
    $dates[] = $date->format('Y-m-d');
	
	// Set array end day
    $now = new DateTime();
	$nowYear = $now->format('Y');
	if ($nowYear == $year) {
		$now->setTime(0, 0);
	} else {
		$now->setDate($year, '12', '31');
		$now->setTime(0, 0);
	}
	
	$interval = new DateInterval('P1D');
    for ($i = 0; ; ++$i) {
        $date->add($interval);
        $dates[] =  $date->format('Y-m-d');
    
        if ($now <= $date) {
            break;
        }
    }

    return $dates;
}

/**
 * Acquire data from the site by date
 * @param $day 2019-01-01
 */
function fiAcquireData($curlOpts, $account, $date) {

	// Make dir
	$year = new DateTime($date);

	if (!is_dir("tmp/$account/".$year->format('Y')))
		mkdir("tmp/$account/".$year->format('Y'));

	$file = sprintf("tmp/%s/%s/%s.html", $account, $year->format('Y'), $date);
	if (is_file($file)) {
		echo "$date already exists<br/>";
		return;
	}

	$url = 'https://investorservice.cfmmc.com/customer/setParameter.do';
	$curlOpts[CURLOPT_URL] = $url;
	$curlOpts[CURLOPT_POST] = true;
	$curlOpts[CURLOPT_HEADER] = false;

	// $date = '2019-12-21';
	$postFields = array(
		'tradeDate' => $date,
		// 'trade' 逐笔 'date' 逐日
		'byType' => 'trade', 
	);

	if (isset($_COOKIE['inputName']) && (isset($_COOKIE['inputValue']))) {
		$postFields[$_COOKIE['inputName']] = $_COOKIE['inputValue'];
	}

	$curlOpts[CURLOPT_POSTFIELDS] = $postFields;

	$ch = curl_init();
	curl_setopt_array($ch, $curlOpts);

	if(!$content = curl_exec($ch))
	{
		trigger_error(curl_error($ch));
	}
	
	file_put_contents($file, $content);

	tidyHtmlTradeData($file, $file);

	echo "$date got<br/>";
}

$opt = isset($_POST['opt']) ? $_POST['opt'] : '';
$opt = isset($_GET['opt']) ? $_GET['opt'] : $opt;
switch ($opt)
{
	case "getVerifyPic":
		fiGetVerify(fiGetCurlOpt());
		exit();
		break;
	case 'login':
		if (!isset($_POST['veriCode']) ) {
			echo 'Verification code is null';
			exit();
		}

		if (!isset($_POST['account']) || !isset($_POST['passwd'])) {
			echo 'account or password is null';
			exit();
		}
	
		fiLogin(fiGetCurlOpt(), $_POST['account'], $_POST['passwd'], 
			$_POST['veriCode']);
		echo 'success';
		break;

	case 'acquireData':

		if (!isset($_POST['year']) || !isset($_POST['account'])) {
			echo 'year or account is null';
			exit();
		}

		$dates = yearDateArray($_POST['year']);
		foreach ($dates as $date) {
			fiAcquireData(fiGetCurlOpt(), $_POST['account'], $date);
		}

		break;
}

exit();
