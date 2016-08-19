<head>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<form action="#" method="post" style="text-align:center;margin-top: 150px;">
    <input class="form-control" type="text" placeholder="Paste yelp.com search link" style="width: 30%;left: 35%;position: relative;" name="query" />
    <br>
    <input type="submit" class="btn btn-primary" value="Download results" name="submit_button" />
</form>

<?php

ini_set("display_errors", 1);

set_time_limit(0);

// register_shutdown_function('dying');




$CONSUMER_KEY = 'hDdWzPS6tV8OJsYyFXnXZg';
$CONSUMER_SECRET = 'aU-rZXP3uHY0k_COucfngAKpQRg';
$TOKEN = 'AqGs34h-i_wZgbW4qt7M_VBRpypKC6-U';
$TOKEN_SECRET = 'dC_xhKoTzSe50kWyXE9luw_81tM';
$API_HOST = 'api.yelp.com';
$SEARCH_LIMIT = 6;
$SEARCH_PATH = '/v2/search/';
$BUSINESS_PATH = '/v2/business/';

if(isset($_POST['submit_button']))
{
    require_once('lib/Ouath.php');
    $url=$_REQUEST['query'];
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    $DEFAULT_TERM = $query['find_desc'];
    $DEFAULT_LOCATION = $query['find_loc'];
    write_headers_csv_file();
    $loop_limit = calculate_total_calls($DEFAULT_TERM, $DEFAULT_LOCATION);
    loop_api_calls($loop_limit, $DEFAULT_TERM, $DEFAULT_LOCATION);
    // require_once 'download.php';
}


/** 
 * Makes a request to the Yelp API and returns the response
 * 
 * @param    $host    The domain host of the API 
 * @param    $path    The path of the APi after the domain
 * @return   The JSON response from the request      
 */
function request($host, $path) {
    $unsigned_url = "https://" . $host . $path;
    // echo $unsigned_url;
    // Token object built using the OAuth library
    $token = new OAuthToken($GLOBALS['TOKEN'], $GLOBALS['TOKEN_SECRET']);

    // Consumer object built using the OAuth library
    $consumer = new OAuthConsumer($GLOBALS['CONSUMER_KEY'], $GLOBALS['CONSUMER_SECRET']);

    // Yelp uses HMAC SHA1 encoding
    $signature_method = new OAuthSignatureMethod_HMAC_SHA1();

    $oauthrequest = OAuthRequest::from_consumer_and_token(
        $consumer, 
        $token, 
        'GET', 
        $unsigned_url
    );
    
    // Sign the request
    $oauthrequest->sign_request($signature_method, $consumer, $token);
    
    // Get the signed URL
    $signed_url = $oauthrequest->to_url();
   
    // Send Yelp API Call
    try {
       
        $ch = curl_init($signed_url);

        if (FALSE === $ch)

            throw new Exception('Failed to initialize');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $data = curl_exec($ch);

        if (FALSE === $data)

            throw new Exception(curl_error($ch), curl_errno($ch));
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if (200 != $http_status)
            throw new Exception($data, $http_status);

        curl_close($ch);
    } catch(Exception $e) {

        trigger_error(sprintf(
            'Curl failed with error #%d: %s',
            $e->getCode(), $e->getMessage()),
            E_USER_ERROR);
    }
    
    return $data;
}

/**
 * Query the Search API by a search term and location 
 * 
 * @param    $term        The search term passed to the API 
 * @param    $location    The search location passed to the API 
 * @return   The JSON response from the request 
 */
function search($term, $location, $offset) {
    $url_params = array();
   
    $url_params['term'] = $term ?: $GLOBALS['DEFAULT_TERM'];
    $url_params['location'] = $location?: $GLOBALS['DEFAULT_LOCATION'];
    // $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
    $url_params['offset'] = $offset;
    $search_path = $GLOBALS['SEARCH_PATH'] . "?" . http_build_query($url_params);
    // echo $search_path;
    return request($GLOBALS['API_HOST'], $search_path);
}

/**
 * Query the Business API by business_id
 * 
 * @param    $business_id    The ID of the business to query
 * @return   The JSON response from the request 
 */
function get_business($business_id) {
    $business_path = $GLOBALS['BUSINESS_PATH'] . urlencode($business_id);
    return request($GLOBALS['API_HOST'], $business_path);
}

function loop_api_calls($loop_limit, $term, $location){
    $offset = 0;
    if ($loop_limit > 50) {
        $loop_limit = 50;
    }
    // $loop_limit = 5;
    // $loop_limit = 1;
    for ($i=0; $i <$loop_limit ; $i++) {
        $response = json_decode(search(urldecode($term), urldecode($location), $offset),true);
        $offset += 20;
        loop_results($response['businesses']);
        // print_r(json_decode($response, true));
        // echo "<pre>";print_r($response);echo "</pre>";
    }
}

function loop_results($results){
    $parent_array = array();
    $limit = 0;
    foreach ($results as $result) {
        $child_array = array();
        echo "S.No:".++$limit."</br>";
        echo "Name :".$result['name']."<br>";
        echo "Phone :".$result['phone']."<br>";
        sleep(3);
         // if ($limit > 1) {
         //    return false;
         // }
        $website = get_website_from_link($result['url']);
        if ($website) {
            $email = get_email_from_site($website);
        }
        if (isset($result['name'])) {
            $child_array[] = $result['name'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['phone'])) {
            $child_array[] = $result['phone'];
        } else {
            $child_array[] = '';
        }
        if (isset($website)) {
            $child_array[] = $website;
        } else {
            $child_array[] = '';
        }
        if (isset($email)) {
            $child_array[] = $email;
        } else {
            $child_array[] = '';
        }
        if (isset($result['display_phone'])) {
            $child_array[] = $result['display_phone'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['location']['display_address'])) {
            $str = '';
            foreach ($result['location']['display_address'] as $value) {
                $str = $value.' '.$str;
            }
            $child_array[] = $str;
        } else {
            $child_array[] = '';
        }
        if (isset($result['url'])) {
            $child_array[] = $result['url'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['snippet_text'])) {
            $child_array[] = $result['snippet_text'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['rating'])) {
            $child_array[] = $result['rating'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['review_count'])) {
            $child_array[] = $result['review_count'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['is_closed'])) {
            $child_array[] = $result['is_closed'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['location']['coordinate']['latitude'])) {
            $child_array[] = $result['location']['coordinate']['latitude'];
        } else {
            $child_array[] = '';
        }
        if (isset($result['location']['coordinate']['longitude'])) {
            $child_array[] = $result['location']['coordinate']['longitude'];
        } else {
            $child_array[] = '';
        }
        // write_into_csv_file($child_array);
    }
}

function calculate_total_calls($term, $location){
    $response = json_decode(search(urldecode($term), urldecode($location), 0),true);
    // print_r($response);
    if (isset($response['total'])) {
        // echo "<pre>";print_r($response);
        $loop_limit = $response['total'] / 20 ;
        return ceil($loop_limit);
    } else {
        return false;
    }
}


function get_website_from_link($link){
    sleep(1);
    include_once('simple_html_dom.php');
    $html = str_get_html(doCall($link));
    $website = false;
    if (empty($html)) {
        echo "No Website Found <hr>";
        return false;
    }
    foreach($html->find('.biz-website a') as $e){
        $website = $e->innertext;
    }
    if ($website) {
        return $website;
    }
    echo "No Website Found <hr>";
    return $website;
}

function get_email_from_site($website){
    if (stripos($website, 'http') === FALSE) {
        $website = 'http://'.$website;
    }
    echo "Website :" . $website."<br>";
    sleep(1);
    $html = str_get_html(doCall($website));
    $email = false;
    if (empty($html)) {
        return false;
    }
    foreach($html->find('a') as $e){
        if (stripos($e->href, 'mailto:') !== FALSE) {
            $email = str_replace('mailto:', '',$e->href);
            break;
        }
    }
    if (!empty($email)) {
        echo "email :".$email."<hr>";
    }
    echo "Email : empty <hr>";
    return $email;
}


function write_into_csv_file($data){
    $fp = fopen('file.csv', 'a+');
    foreach ($data as $fields) {
        fputcsv($fp, $data);
    }
    fclose($fp);
}

function write_headers_csv_file(){
    $list = array (
        array('Name', 'Phone', 'Website', 'Email' ,'Display Phone', 'Location Address', 'URL', 'Snippet Text', 'Rating', 'Review Count','Is Closed' ,'Location latitude', 'Location longitude'),
    );

    $fp = fopen('file.csv', 'w');

    foreach ($list as $fields) {
        fputcsv($fp, $fields);
    }

    fclose($fp);

}

function doCall($URL) //Needs a timeout handler
{
    $SSLVerify = false;
    $URL = trim($URL);
    if(stripos($URL, 'https://') !== false){ $SSLVerify = true; }

    $HTTPCustomHeaders = array();


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

    if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    }

    if(!empty($options['httpAuth'])){
        curl_setopt($ch, CURLOPT_USERPWD, $options['httpAuth']['username'].':'.$options['httpAuth']['password']);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
    }

    if(!empty($options['useCookie'])){
        if(!empty($options['cookie'])){
            curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
        }
    }

    @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);


    $rawResponse      = curl_exec($ch);


    curl_close($ch);


    return $rawResponse;
}

function dying(){
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}
?>

