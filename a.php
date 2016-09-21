<?php

register_shutdown_function('diee');

require_once('simple_html_dom.php');

function diee(){
     echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}

$result = get_neighbourhoods('https://www.yelp.com/search?find_desc=Restaurants&find_loc=Washington+DC%2C+DC%2C+USA&ns=1');

function get_neighbourhoods($query){
    sleep(1);
    $html = str_get_html(doCall($query));
    $website = false;
    if (empty($html)) {
        echo "No Website Found <hr>";
        die('Erro : Cannot get neighbourhoods');
    }
    $neighbourhoods = array();
    foreach($html->find('.place input') as $e){
        $result = explode('::', $e->value);
        if (empty($result[1])) {
            $result2 = explode(':', $e->value);
            $neighbourhoods[] = $result2[1];
        } else {
            $neighbourhoods[] = $result[1];
        }
    }
    return $neighbourhoods;
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
    echo "<pre>";
    //print_r($rawResponse);
    echo "</pre>";
    curl_close($ch);
    return $rawResponse;

}

