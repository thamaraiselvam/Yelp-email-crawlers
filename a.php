<?php
require_once('simple_html_dom.php');

get_email_from_site('http://thechesapeakeroom.com/contact');

function get_email_from_site($website){
    echo "Parsing email from main page...<br />";
    if (stripos($website, 'http') === FALSE) {
        $website = 'http://'.$website;
    }
    echo "Website :" . $website."<br>";
    sleep(1);
    $email = parse_email($website);
    if (empty($email)) {
        echo "Deep searching email ...<br />";
        $email = deep_email_search($website);
    }
    if ($email) {
        echo "Email : ".$email . "<br/>";
    } else {
        echo "Email : Not found <br/>";
    }
    return $email;
}

function parse_email($link){
	echo "Parsing : ".$link."<br>";
    $text = doCall($link);
    if (!empty($text)) {
        $res = preg_match_all(
            "/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i",
            $text,
            $matches
        );
        if ($res) {
            foreach(array_unique($matches[0]) as $email) {
                return $email;
            }
        }
        else {
            return false;
        }
    }
}

function deep_email_search($website){
    echo "Parsing other pages...<br />";
    sleep(2);
    $html = str_get_html(doCall($website));
    $email = false;
    if (empty($html)) {
        return false;
    }
    $email = false;
    foreach($html->find('a') as $e){
        if (stripos($e->href, 'contact') !== FALSE || stripos($e->href, 'about') !== FALSE || stripos($e->href, 'impressum') !== FALSE) {
        	sleep(3);
        	$deep_link = make_valid_url($website, $e->href);
            $email = parse_email($deep_link);
            if ($email) {
                break;
            }
        }
    }
    return $email;
}

function make_valid_url($website, $deep_link){
	if (stripos($deep_link, 'http') !== FALSE) {
		echo "valid URL :".$deep_link."</br>";
		return $deep_link;
	}
	$parsed_url = parse_url($deep_link);
	if (empty($parsed_url['host'])) {
		$link = addTrailingSlash($website).removeBeginningSlash($deep_link);
		echo "Formed valid URL :".$link."</br>";
		return $link;
	}
}

function addTrailingSlash($string) {
	return removeTrailingSlash($string) . '/';
}

function removeTrailingSlash($string) {
	return rtrim($string, '/');
}

function removeBeginningSlash($string) {
	return ltrim($string, '/');
}

function doCall($URL) //Needs a timeout handler
{
    $SSLVerify = false;
    $URL = trim($URL);
    if(stripos($URL, 'https://') !== false){ $SSLVerify = true; }
    $ch = curl_init($URL);
    curl_setopt($ch, CURLOPT_URL, $URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($SSLVerify === true) ? 2 : false );
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSLVerify);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_HEADER, true);
    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $rawResponse      = curl_exec($ch);
    curl_close($ch);
    return $rawResponse;
}

