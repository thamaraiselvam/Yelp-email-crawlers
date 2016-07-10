<head>
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
</head>
<form action="#" method="post" style="text-align:center;margin-top: 150px;">
    <input class="form-control" type="text" placeholder="Paste yelp.com search link" style="width: 30%;left: 35%;position: relative;" name="query" />
    <br>
    <input type="submit" class="btn btn-primary" value="Download results" name="submit_button" />
</form>

<?php

ini_set("display_errors", 0);

set_time_limit(0);
$CONSUMER_KEY = '9eMvv6vSaZLVUPKBkjhjwg';
$CONSUMER_SECRET = 'PhBHXplc6DwAwZ4FcQcD-lgw49E';
$TOKEN = 'kYn5sDa4gzyRMmB_MzYuw6jxpSu9Tfuu';
$TOKEN_SECRET = 's-eWVTPNcNgj-82HnOpm9o1Y320';
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
    require_once 'download.php';
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
    echo $search_path;
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
    // $loop_limit = 1;
    for ($i=0; $i <$loop_limit ; $i++) {
        $response = json_decode(search(urldecode($term), urldecode($location), $offset),true);
        $offset += 20;
        loop_results($response['businesses']);
        //print_r(json_decode($response, true));
        // echo "<pre>";print_r($response);
    }
}

function loop_results($results){
    $parent_array = array();
    foreach ($results as $result) {
        $child_array = array();
        // echo "<pre>";
        // print_r($result);

        // echo "</pre>";
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
    // echo "<pre>";
    // print_r($child_array);
    // echo "</pre>";
    write_into_csv_file($child_array);
    }
}

function calculate_total_calls($term, $location){
    $response = json_decode(search(urldecode($term), urldecode($location), 0),true);
    // echo $response['total'];
    if (isset($response['total'])) {
        // echo "<pre>";print_r($response);
        $loop_limit = $response['total'] / 20 ;
        return ceil($loop_limit);
    } else {
        return false;
    }
}

function write_into_csv_file($data){
    $fp = fopen('file.csv', 'a+');
    // foreach ($data as $fields) {
        fputcsv($fp, $data);
    // }
    fclose($fp);
}

function write_headers_csv_file(){
    $list = array (
        array('Name', 'Phone', 'Display Phone', 'Location Address', 'URL', 'Snippet Text', 'Rating', 'Review Count','Is Closed' ,'Location latitude', 'Location longitude'),
    );

    $fp = fopen('file.csv', 'w');

    foreach ($list as $fields) {
        fputcsv($fp, $fields);
    }

    fclose($fp);

}
?>

