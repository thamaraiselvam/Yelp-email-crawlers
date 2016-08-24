<?php
// phpinfo();


###############################################################
# Email Extractor 1.0
###############################################################
# Visit http://www.zubrag.com/scripts/ for updates
############################################################### 
ini_set("display_errors", 1);
register_shutdown_function('dying');
write_headers_csv_file();

function write_headers_csv_file(){
    $list = array (
        array('Name', 'Phone', 'Website', 'Email' ,'Display Phone', 'Location Address', 'URL', 'Snippet Text', 'Rating', 'Review Count','Is Closed' ,'Location latitude', 'Location longitude'),
    );
    $file = getcwd().'/files.csv';
    echo $file;
    $fp = fopen( $file ,'a+');

    foreach ($list as $fields) {
        fputcsv($fp, $list);
    }

    fclose($fp);

}


function dying(){
    echo "<pre>";
    print_r(error_get_last());
    echo "</pre>";
}

// $the_url = isset($_REQUEST['url']) ? htmlspecialchars($_REQUEST['url']) : '';
// 


// if (isset($_REQUEST['url']) && !empty($_REQUEST['url'])) {
//   // fetch data from specified url
//   $text = file_get_contents($_REQUEST['url']);
// }
// elseif (isset($_REQUEST['text']) && !empty($_REQUEST['text'])) {
//   // get text from text area
//   $text = $_REQUEST['text'];
// }

// // parse emails
// if (!empty($text)) {
//   $res = preg_match_all(
//     "/[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}/i",
//     $text,
//     $matches
//   );
//   print_r($matches);
//   if ($res) {
//     foreach(array_unique($matches[0]) as $email) {
//       echo $email . "<br />";
//     }
//   }
//   else {
//     echo "No emails found.";
//   }
// }


// include_once('simple_html_dom.php');
// $html = str_get_html(doCall('https://www.yelp.com/search?find_desc=Restaurants&find_loc=Washington+DC%2C+DC%2C+USA&ns=1'));
// $website = false;
// if (empty($html)) {
//     echo "No Website Found <hr>";
//     exit;
// }
//     $neighbourhood = array();
// foreach($html->find('.place input') as $e){
//     $result = explode('::', $e->value);
//     if (empty($result[1])) {
// 	    $result2 = explode(':', $e->value);
// 	    $neighbourhood[] = $result2[1];
//     } else {
// 	    $neighbourhood[] = $result[1];
//     }
// }

// echo "<pre>";
// print_r($neighbourhood);
// echo "</pre>";

// function doCall($URL) //Needs a timeout handler
// {
//     $SSLVerify = false;
//     $URL = trim($URL);
//     if(stripos($URL, 'https://') !== false){ $SSLVerify = true; }

//     $HTTPCustomHeaders = array();


//     $ch = curl_init($URL);
//     curl_setopt($ch, CURLOPT_URL, $URL);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//     curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
//     curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
//      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36');
//     curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, ($SSLVerify === true) ? 2 : false );
//     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $SSLVerify);
//     curl_setopt($ch, CURLOPT_TIMEOUT, 30);
//     curl_setopt($ch, CURLOPT_HEADER, true);

//     if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
//         curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
//     }

//     if(!empty($options['httpAuth'])){
//         curl_setopt($ch, CURLOPT_USERPWD, $options['httpAuth']['username'].':'.$options['httpAuth']['password']);
//         curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
//     }

//     if(!empty($options['useCookie'])){
//         if(!empty($options['cookie'])){
//             curl_setopt($ch, CURLOPT_COOKIE, $options['cookie']);
//         }
//     }

//     @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);


//     $rawResponse      = curl_exec($ch);


//     curl_close($ch);


//     return $rawResponse;
// }