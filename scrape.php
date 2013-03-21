<!DOCTYPE html>
<html>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
</head>
<body>
<?

require('class.DB.php');

/**
 * Get the date! Accepts days of the week, months, and dates, e.g., Tuesday, March 13
 * @param String $content 
 * @return The date found - false if none
 */
function get_date($content) {
	$pattern = '/(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday)([, ]+)(\d|January|February|March|April|May|June|July|August|September|October|November|December)(\/*)( *)(\d*)/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[0];

	return false;
}

/**
 * Get the ticket link. It basically grabs any link that has the word "ticket" in it. Not very elegant, I know..
 * @param String $content - HTML of the page we grabbed 
 * @return the link URL. False if none found.
 */
function get_ticket_link($content) {
	$pattern = '/a href="(.+(ticket).+)"\s/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[1];

	return false;
}

/**
 * Get the time! Patterns it will grab:
 *	7pm
 *	7 pm
 *	7:30pm
 *	7:30 pm
 *	7:30
 **/
function get_time($content) {
	$pattern = '/((\d{1,2})pm|(\d{1,2}) pm|(\d{1,2}:\d{2})pm|(\d{1,2}:\d{2}) pm|(\d{1,2}:\d{2}))/i';
	preg_match($pattern, $content, $matches);

	if (count($matches))
		return $matches[0];

	return false;
}

/**
 * Get all the topics! _o/
 * @param String $content - the HTML of the page we just scraped 
 * @return Array of matches -- false is none
 */
function get_topics($content) {

	// Limit the content to just stuff inside body tags. Don't want scripts screwing up the regexes
	$content = array_pop(explode('<body>', $content));

	// Strip out tags. We don't care
	$content = strip_tags($content);

	// Replace "smart" or "curly" quotes with straight, normal, dumb quotes
	$content = preg_replace('/“|”|&#8220;|&#8221;/', '"', $content);
	$content = preg_replace('/’|&#8217;/', "'", $content);

	// Remove commas
	$content = str_replace(',', '', $content);

	// There was a typo in the SF Nerd Nite where there was no space between the " and by
	$content = str_replace('"by', '" by', $content);

	/**
	 * Let's find some shit!
	 **/
	// "Some topic" by Joe Schmoe
	$pattern = '/"(.+)"(\W|\r|\n|[:&!?])+by\s+(.+\S){1,2}/i';
	preg_match_all($pattern, $content, $matches);

	$matches = array_filter($matches);
	if (count($matches))
		return $matches[0];

	// #1: Using data to change the world
	$pattern = '/#\d{1}[.:-](.+)(\r|\n)/i';
	preg_match_all($pattern, $content, $matches);

	$matches = array_filter($matches);
	if (count($matches))
		return $matches[0];

	return false;
}

if (array_key_exists('site', $_GET)) {
	$sites = array($_GET['site']);
} else {
	$sites = array('austin', 'eastbay', 'sf', 'amsterdam', 'auckland', 'berlin', 'boston', 'brighton', 'chicago', 'dc', 'detroit', 'duluth', 'edmonton','honolulu','ithaca','kansascity', 'la', 'nola', 'nyc', 'orlando', 'philadelphia', 'phoenix', 'portland', 'sandiego', 'seattle', 'toronto');
}

// Connect to DB
$db = DB::connect();

// Loop through each site, scrape it, and throw data in to the DB
$site_data = array();
foreach ($sites as $site) {
	$url = 'http://'.$site.'.nerdnite.com';

	// Fetch the website, Watson!
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION , 1); // TRUE
	curl_setopt($ch, CURLOPT_HEADER ,0); // DO NOT RETURN HTTP HEADERS
	curl_setopt($ch, CURLOPT_RETURNTRANSFER ,1); // RETURN THE CONTENTS OF THE CALL
	curl_setopt( $ch, CURLOPT_ENCODING, "UTF-8" ); 
	$data = curl_exec($ch);
	$data = strtolower($data);
	$content = $data;

	/**
	 * Find data, if possible
	 **/
	if ($date = get_date($content))
		$site_data[$site]['date'] = $date;

	if ($ticket = get_ticket_link($content))
		$site_data[$site]['ticket_link'] = $ticket;

	if ($time = get_time($content))
		$site_data[$site]['time'] = $time;

	if ($topics = get_topics($content)) {
		$site_data[$site]['topics'] = $topics;
	}

	/**
	 * Clean up the data and insert it into the DB
	 **/
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