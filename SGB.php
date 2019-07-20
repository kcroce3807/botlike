<?php
date_default_timezone_set("Asia/Jakarta");
$reaction_type = 'Like'; //Like Love HaHa Wow Sad Angry
$useragent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:68.0) Gecko/20100101 Firefox/68.0';
$cookie = '__cfduid=dde4ed183281733998a1d999f2a6589911563458092; mode=day; src=1; user_id=8a99b4881a9b08d99de9fbf8ca6f4f05a3eeb4349fe8a8107599b49b491fcdd9a6bb0fe8210318111a3650aedfdd3a21444047ed2d89458f; cookieconsent_status=dismiss; PHPSESSID=a8f9fa52aac395d42cade9c6b45c9575; ad-con=a%3A2%3A%7Bs%3A4%3A%26quot%3Bdate%26quot%3B%3Bs%3A10%3A%26quot%3B2019-07-20%26quot%3B%3Bs%3A3%3A%26quot%3Bads%26quot%3B%3Ba%3A0%3A%7B%7D%7D; access=1; _ga=GA1.2.843986441.1563625493; _gid=GA1.2.1971423498.1563625493; last_sidebar_update=1563626731; _us=1563713466'; //sgb social cookie

/*
* sgb like proccess
*/
$feed = sgb_get_feed($useragent, $cookie);
$feed_session = sgb_session_feed($useragent, $cookie);
foreach ($feed as $key => $post_id) {
	if(sgb_log($post_id) == true){
		$post = sgb_post_like($feed_session, $post_id, $reaction_type, $useragent, $cookie);
		if ($post->status == 200) {
			$status = '<font style="background-color:green;color:white;">Success</font>';
		} else {
			$status = '<font style="background-color:red;color:white;">Failed</font>';
		}
		print '<pre>'.print_r('PostID: '.$post_id.'<br>Status Like: '.$status, 1).'</pre>'; flush();
	}
}

/*
* sgb get session feed
*/
function sgb_session_feed($useragent, $cookie) {
	$get = sgb_curl('https://social.sgbteam.id/', 0, 0, $useragent, $cookie)[1];
	return preg_match_all('/class="main_session" value="(.*?)"/', $get, $result) ? end($result[1]) : null;
}
/*
* sgb get feed
*/
function sgb_get_feed($useragent, $cookie) {
	$get = sgb_curl('https://social.sgbteam.id/requests.php?f=load_posts', sgb_header('social.sgbteam.id', 'https://social.sgbteam.id/'), 0, $useragent, $cookie)[1];
	return preg_match_all('/data-post-id="(.*?)"/', $get, $post_id) ? array_unique($post_id[1]) : null;
}
/*
* sgb log
*/
function sgb_log($post_id) {
	if (file_exists('sgb_log.txt')) {
		$logdata = json_encode(file('sgb_log.txt'));
	} else {
		$logdata = '';
	}
	if (!preg_match("/" . $post_id . "/", $logdata)) {
		$x = $post_id . "\n";
        $y = fopen('sgb_log.txt', 'a');
        fwrite($y, $x);
        fclose($y);
		$result = true;
	} else {
		$result = false;
	}
	return $result;
}
/*
* sgb post like
*/
function sgb_post_like($feed_session, $post_id, $reaction_type, $useragent, $cookie) {
	$post = json_decode(sgb_curl('https://social.sgbteam.id/requests.php?hash='.$feed_session.'&f=posts&s=register_reaction&post_id='.$post_id.'&reaction='.$reaction_type.'&_='.sgb_strtotime('now'), sgb_header('social.sgbteam.id', 'https://social.sgbteam.id/'), 0, $useragent, $cookie)[1]);
	return $post;
}
/*
* sgb header
*/
function sgb_header($host, $referer) {
	$header = array(
		'Host: '.$host,
		'Accept: */*',
		'Accept-Language: en-US,en;q=0.5',
		'Referer: '.$referer,
		'X-Requested-With: XMLHttpRequest',
		'DNT: 1',
		'Connection: keep-alive',
		'TE: Trailers'
	);
	return $header;
}
/*
* sgb strtotime
*/
function sgb_strtotime($datetime){
	$date_now = strtotime($datetime);
	return $date_now;
}
/*
* curl function
*/
function sgb_curl($url, $header = null, $postfields = null, $useragent = null, $cookie = null, $proxy = null) {
    $c = curl_init();
    if($proxy) curl_setopt($c, CURLOPT_PROXY, $proxy);
    curl_setopt($c, CURLOPT_URL, $url); 
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false); 
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($c, CURLOPT_TIMEOUT, 10);
    if($header) curl_setopt($c, CURLOPT_HTTPHEADER, $header);
    if($postfields) curl_setopt($c, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($c, CURLOPT_HEADER, 1);
    if($cookie) curl_setopt($c, CURLOPT_COOKIE, $cookie);
    if($useragent) curl_setopt($c, CURLOPT_USERAGENT, $useragent);
    $response = curl_exec($c);
    $header = substr($response, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    $body = substr($response, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    curl_close($c);
    return array($header, $body);
}
?>