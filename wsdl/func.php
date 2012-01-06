<?php include('db.php');
function agents(){
	return array('Pingdom.com','SiteUptime.com','dotnetdotcom.org','Yahoo','Baiduspider','Yandex','MJ12bot','Google',
		'LinkMarketbot','Java','Sosospider','80legs','solfo.com','textmode','Jeeves');
}
function cities(){
	return array('BEIJING','GUANGDONG','GUANGZHOU','SHENZHEN','SHANGHAI','MOSCOW');
}
function tracking(){
	$ip=$_SERVER['REMOTE_ADDR'];
	if(blocked($ip)){
		header("HTTP/1.0 404 Not Found");
		echo("HTTP/1.0 404 Not Found");
		exit;
	}
	if($_COOKIE['tracking']==null){		
		include('browscap.php');
		$bc = new Browscap('./tmp');
		$current_browser = $bc->getBrowser();
		$keyword = search_engine_query_string();
		$landing = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		$referer = $_SERVER['HTTP_REFERER'];
		$medium=trackmedium($landing,$referer,$keyword);
		$useragent = $_SERVER['HTTP_USER_AGENT'];
		$agents = agents(); $bot = null;
		$key = ''; //ipinfodb.com key
		$link = 'http://api.ipinfodb.com/v3/ip-city/?key='.$key.'&ip='.$ip;
		//$browseragent= 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.30 (KHTML, like Gecko) Chrome/12.0.742.122 Safari/534.30';
		//$browseragent= 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0';
		$browseragent='Bot';
		if (function_exists('curl_init')) {
			$ch = curl_init(); // initialize a new curl resource			
			curl_setopt($ch, CURLOPT_URL, $link); // set the url to fetch
			curl_setopt($ch, CURLOPT_HEADER, 0); // don't give me the headers just the content
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the value instead of printing the response to browser
			curl_setopt($ch, CURLOPT_USERAGENT, $browseragent); // mimic a browser
			$content = curl_exec($ch); 
			$read = explode (';',$content);
			curl_close($ch);    // close the session  
		}else{
			$xml = @file_get_contents($link);
			$read = explode (';',$xml);	
		}
		foreach($agents as $agent){
			$pos = strpos($useragent,$agent);
			if($pos !== false) {
				$bot = "yes";
			}
		}
		$cities=cities();
		foreach ($cities as $city){
			if($read[6]==$city){
				header("HTTP/1.0 404 Not Found");
				echo("HTTP/1.0 404 Not Found");
				exit;
			}
		}
		if($current_browser['Parent']=="General Crawlers"){ $bot = "yes"; }
		if($current_browser['Parent']=="Search Engines"){ $bot = "yes"; }
		if($current_browser['Parent']=="MSN"){ $bot = "yes"; }
		if(!$bot){
			localdb_connect();
			$query = sprintf("insert into `track` set `keyword` = '%s', `landing` = '%s',
				`referer` = '%s', `useragent` = '%s', `ip` = '%s', `os` = '%s', `browser` = '%s', `medium` = '%s' ,`region` = '%s',`city` = '%s' ", //
				mysql_real_escape_string($keyword),
				mysql_real_escape_string($landing),
				mysql_real_escape_string($referer),
				mysql_real_escape_string($useragent),
				mysql_real_escape_string($ip),
				mysql_real_escape_string($current_browser['Platform']),
				mysql_real_escape_string($current_browser['Parent']),
				mysql_real_escape_string($medium),
				mysql_real_escape_string($read[5]),
				mysql_real_escape_string($read[6]));
			$result = mysql_query($query);
			$expire=time()+60*60*24*7;
			$id = mysql_insert_id();
			setcookie('tracking',$id,$expire);
		}
	}
}
function trackmedium($landing,$referer,$keyword){
	$pos = strpos($landing,"gclid");
	if($pos === false) {
		if($referer!=null){
			if($keyword){
				$medium = "Organic";
			}else{
				$medium = "Referal";
			}
		}else{
			$medium = "Direct";
		}
	}else{
		$medium = "AdWords";
	}
	return $medium;
}
function sendAnalytics($cid){
	$results = getAnalytics();
	$results = $results[0];
	remotedb_connect();
	$query = sprintf("insert into `track` set `cid` = '%s',`keyword` = '%s', `landing` = '%s',
		`referer` = '%s', `useragent` = '%s', `ip` = '%s', `timestamp` = '%s', `os` = '%s', 
		`browser` = '%s',`medium` = '%s',`region` = '%s',`city` = '%s' ",
		mysql_real_escape_string($cid),
		mysql_real_escape_string($results['keyword']),
		mysql_real_escape_string($results['landing']),
		mysql_real_escape_string($results['referer']),
		mysql_real_escape_string($results['useragent']),
		mysql_real_escape_string($results['ip']),
		mysql_real_escape_string($results['timestamp']),
		mysql_real_escape_string($results['os']),
		mysql_real_escape_string($results['browser']),
		mysql_real_escape_string($results['medium']),
		mysql_real_escape_string($results['region']),
		mysql_real_escape_string($results['city']));
	$result = mysql_query($query);
	remotedb2_connect();
	$query = sprintf("insert into `track` set `cid` = '%s',`keyword` = '%s', `landing` = '%s',
		`referer` = '%s', `useragent` = '%s', `ip` = '%s', `timestamp` = '%s', `os` = '%s', 
		`browser` = '%s',`medium` = '%s',`region` = '%s',`city` = '%s' ",
		mysql_real_escape_string($cid),
		mysql_real_escape_string($results['keyword']),
		mysql_real_escape_string($results['landing']),
		mysql_real_escape_string($results['referer']),
		mysql_real_escape_string($results['useragent']),
		mysql_real_escape_string($results['ip']),
		mysql_real_escape_string($results['timestamp']),
		mysql_real_escape_string($results['os']),
		mysql_real_escape_string($results['browser']),
		mysql_real_escape_string($results['medium']),
		mysql_real_escape_string($results['region']),
		mysql_real_escape_string($results['city']));
	$result = mysql_query($query);
}
function getAnalytics(){
	$connection = localdb_connect();
	$query = sprintf("select * from `track` where `id` = '%s'",
		mysql_real_escape_string($_COOKIE['tracking']));
	$result = mysql_query($query);
	$results = db_result($result);
	return $results;
}
function blocked($ip){
	$connection = localdb_connect();
	$query = sprintf("select * from `block` where `ip` = '%s'",
		mysql_real_escape_string($ip));
	$result = mysql_query($query);
	$results = db_result($result);	
	if($results){ return $results;
	}else{ return false; }
}
function search_engine_query_string($url = false) {
    if(!$url && !$url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false) {
        return '';
    }
    $parts_url = parse_url($url);
    $query = isset($parts_url['query']) ? $parts_url['query'] : (isset($parts_url['fragment']) ? $parts_url['fragment'] : '');
    if(!$query) {
        return '';
    }
    parse_str($query, $parts_query);
    return isset($parts_query['q']) ? $parts_query['q'] : (isset($parts_query['p']) ? $parts_query['p'] : '');
}
function webRequest($name,$phone,$aphone,$windows,$doors,$time,$day,$email){
	$pattrn = array(" ", "-", ".", "(", ")", "_");
	$newphone = str_replace($pattrn,'',$phone);
	$newphone = substr_replace($newphone,'-',3,0);
	$newphone = substr_replace($newphone,'-',7,0);
	if($aphone){$newaphone = str_replace($pattrn,'',$aphone);
	$newaphone = substr_replace($newaphone,'-',3,0);
	$newaphone = substr_replace($newaphone,'-',7,0);}
	remotedb_connect();
	$date = date('m/d/Y');
	$query = sprintf("insert into `contact` set `name` = '%s', 
		`phone` = '$newphone',
		`aphone` = '$newaphone',
		`doors` = '$doors',
		`windows` = '$windows',
		`time` = '$time',
		`day` = '$day',
		`email` = '$email',
		`status` = 'new',
		`recieved` = '$date'",
		mysql_real_escape_string($name)) ;
	$result = mysql_query($query);
	remotedb2_connect();
	$date = date('m/d/Y');
	$query = sprintf("insert into `contact` set `name` = '%s', 
		`phone` = '$newphone',
		`aphone` = '$newaphone',
		`doors` = '$doors', 
		`windows` = '$windows',
		`time` = '$time',
		`day` = '$day',
		`email` = '$email',
		`status` = 'new',
		`recieved` = '$date'",
		mysql_real_escape_string($name)) ;
	$result = mysql_query($query);
	return mysql_insert_id();
}
function db_result($result) {
	$result_array = array();   
        for ($i=0; @$row = mysql_fetch_array($result) ; $i++){
       	   $result_array[$i] = $row;
       	}	
	return $result_array;
}
?>