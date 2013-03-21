<!DOCTYPE html>
<html>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
</head>
<body>
<?

require('class.DB.php');

function get_date($content) {
	$pattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)([, ]+)(\d|January|February|March|April|May|June|July|August|September|October|November|December)(\/*)( *)(\d*)/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[0];

	return false;
}

function get_ticket_link($content) {
	$pattern = '/a href="(.+(ticket).+)"\s/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[1];

	return false;
}

function get_time($content) {
	$pattern = '/((\d{1,2})pm|(\d{1,2}) pm|(\d{1,2}:\d{2})pm|(\d{1,2}:\d{2}) pm|(\d{1,2}:\d{2}))/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[0];

	return false;
}

function get_topics($content) {
	$content = array_pop(explode('<body>', $content));
	$content = strip_tags($content);
	$content = preg_replace('/“|”|&#8220;|&#8221;/', '"', $content);
	$content = preg_replace('/’|&#8217;/', "'", $content);
	$content = str_replace(',', '', $content);
	$content = str_replace('"by', '" by', $content);
	// Get a pattern like:  "Some topic" by Joe Schmoe
	$pattern = '/"(.+)"(\W|\r|\n|[:&!?])+by\s+(.+\S){1,2}/i';
	preg_match_all($pattern, $content, $matches);

	$matches = array_filter($matches);
	if (count($matches))
		return $matches[0];

	// Get a pattern like: #1: Using data to change the world
	$pattern = '/#\d{1}[.:-](.+)(\r|\n)/i';
	preg_match_all($pattern, $content, $matches);

	$matches = array_filter($matches);
	if (count($matches))
		return $matches[0];


	return false;
}

function convert_smart_quotes($string) {
	$search = array(chr(145),
	chr(146),
	chr(147),
	chr(148),
	chr(151));

	$replace = array("'",
	"'",
	'"',
	'"',
	'-');

	$string = str_replace($search, $replace, $string, $count);
	return $string;
}

if (array_key_exists('site', $_GET)) {
	$sites = array($_GET['site']);
} else {
	$sites = array('austin', 'eastbay', 'sf', 'amsterdam', 'auckland', 'berlin', 'boston', 'brighton', 'chicago', 'dc', 'detroit', 'duluth', 'edmonton','honolulu','ithaca','kansascity', 'la', 'nola', 'nyc', 'orlando', 'philadelphia', 'phoenix', 'portland', 'sandiego', 'seattle', 'toronto');
}

$db = DB::connect();
$site_data = array();
foreach ($sites as $site) {
	$url = 'http://'.$site.'.nerdnite.com';

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1); // TRUE
	curl_setopt($ch, CURLOPT_HEADER ,0); // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" ); 
	$data = curl_exec($ch);
	$data = strtolower($data);
	$content = $data;

	if ($date = get_date($content))
		$site_data[$site]['date'] = $date;

	if ($ticket = get_ticket_link($content))
		$site_data[$site]['ticket_link'] = $ticket;

	if ($time = get_time($content))
		$site_data[$site]['time'] = $time;

	if ($topics = get_topics($content)) {
		$site_data[$site]['topics'] = $topics;
	}

	$data = $site_data[$site];
	$city = $site;
	$event_date = date('Y-m-d ', strtotime($data['date'])).' '.$data['time'];
	$ticket_link = DB::clean_string($data['ticket_link']);
	$topics = DB::clean_string(json_encode($data['topics']));

	$sql = 'INSERT INTO events SET
		city = "'.$city.'",
		event_date = "'.$event_date.'",
		ticket_link = "'.$ticket_link.'",
		topics = \''.$topics.'\'
		ON DUPLICATE KEY UPDATE
			event_date = "'.$event_date.'",
			ticket_link = "'.$ticket_link.'",
			topics = \''.$topics.'\'';
	DB::update($sql);
}

?>
</body>
</html>