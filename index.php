<?php
require ("whoisClass.php");
$domain = $_GET['domain'];
$domain = strtolower(trim($domain));
$domain = preg_replace('/ /i', '', $domain);
$domain = preg_replace('/^http:\/\//i', '', $domain);
$domain = preg_replace('/^https:\/\//i', '', $domain);
$domain = explode('/', $domain);
$domain = trim($domain[0]);
if (substr_count($domain, ".") == 2) {
    $dotpos = strpos($domain, ".");
    $domtld = strtolower(substr($domain, $dotpos + 1));
    $whoisserver = $whoisservers[$domtld];
    if (!$whoisserver) {
        if (strpos($domain, "www") === false) {
        } else {
            $domain = preg_replace('/^www\./i', '', $domain);
        }
    }
}
function LookupDomain($domain) {
    global $whoisservers;
    $whoisserver = "";
    $dotpos = strpos($domain, ".");
    $domtld = strtolower(substr($domain, $dotpos + 1));
    $whoisserver = $whoisservers[$domtld];
    if (!$whoisserver) {
        return "Error: No appropriate Whois server found for <b>$domain</b> domain!";
    }
    //if($whoisserver == "whois.verisign-grs.com") $domain = "=".$domain; // whois.verisign-grs.com requires the equals sign ("=") or it returns any result containing the searched string.
    $result = QueryWhoisServer($whoisserver, $domain);
    if (!$result) {
        return "Error: No results retrieved $domain !";
    }
    preg_match("/Whois Server: (.*)/", $result, $matches);
    $secondary = $matches[1];
    if ($secondary) {
        $result = QueryWhoisServer($secondary, $domain);
    }
    return $result;
}
function QueryWhoisServer($whoisserver, $domain) {
    $port = 43;
    $timeout = 10;
    $fp = @fsockopen($whoisserver, $port, $errno, $errstr, $timeout) or die("<pre>\nSocket Error " . $errno . " - " . $errstr);
    fputs($fp, $domain . "\r\n");
    $out = "";
    while (!feof($fp)) {
        $out.= fgets($fp);
    }
    fclose($fp);
    return $out;
}
?>
<html>
  
  <head>
    <title>域名whois查询</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" type="text/css" href="./style.css"></head>
  
  <body>
    <div class="form_div">
      <form name="form" id="myform" action="<?php
      $_SERVER['PHP_SELF']; ?>" method="get">
        <input type="text" class="input" maxlength="100" name="domain" value="" size="30">
        <input class="formBtn" type="submit" value="Whois" /></form>
    </div>
    <div class="response">
		<pre><?php
		if ($domain) {
			if (preg_match("/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/", $domain)) {
				$result = QueryWhoisServer("whois.apnic.net", $domain);
				//whois.apnic.net whois.lacnic.net
				echo $result;
			} elseif (!preg_match("/^([-a-zA-Z0-9]{1,100})\.([a-z\.]{2,})$/i", $domain)) {
				die("Error:Wrong format!");
			} else {
				$result = LookupDomain($domain);
				echo $result;
			}
		}
		?></pre>
    </div>
  </body>

</html>