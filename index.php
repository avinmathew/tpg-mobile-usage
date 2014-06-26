<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TPG Mobile Usage</title>
  </head>
  <body>
<?
function connect($url, $postData = "")
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.100 Safari/534.30');
    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    if ($postData != "") {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
    }
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_COOKIEFILE, __DIR__.'/cookies.txt' );
    curl_setopt($curl, CURLOPT_COOKIEJAR,  __DIR__.'/cookies.txt' );
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$username = "";
$password = "";
$account_number = "";

// Login
$postData = array("check_username" => $username, "password" => $password);
$response = connect("https://cyberstore.tpg.com.au/your_account/", $postData);

// Get mobile usage page
$postData = array("viewdetails-".$account_number => "Mobile Usage");
$response = connect("https://cyberstore.tpg.com.au/your_account/index.php?function=view_all_mobile", $postData);

// Find links to monthly transactions
$pattern = "/<A HREF=\"(.*)\">Display charges/";
preg_match_all($pattern, $response, $urlMatches);

// Try find data on last link
$path = end($urlMatches[1]);
$url = "https://cyberstore.tpg.com.au/your_account/".$path;
$response = connect($url);
$cap_pattern = "/Any Network Cap remaining: (.*)&nbsp;&nbsp;TPG/";
$data_pattern = "/Free Data remaining: (.*)&nbsp;&nbsp;/";
preg_match($cap_pattern, $response, $cap_matches);
preg_match($data_pattern, $response, $data_matches);

// Try find data on second last link if we couldn't find it on first link
if (empty($cap_matches)) {
  $path = prev($urlMatches[1]);
  $url = "https://cyberstore.tpg.com.au/your_account/".$path;
  $response = connect($url);
  preg_match($cap_pattern, $response, $cap_matches);
  preg_match($data_pattern, $response, $data_matches);
}

$startPos = strpos($response, '<!-- WARNING: User interface subject to change without notice.');
$endPos = strpos($response, '<div class="btm">');

echo substr($response, $startPos, $endPos - $startPos);
?>
  </body>
</html>