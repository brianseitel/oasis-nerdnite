<?
require('class.DB.php');

$data = DB::getArray('SELECT city, event_date, ticket_link, topics FROM events ORDER BY city');
header('Content-type: application/json');

$results = array();
foreach ($data as $event) {
	$results[] = array(
		'city' => $event['city'],
		'event_date' => $event['event_date'],
		'ticket_link' => $event['ticket_link'],
		'topics' => json_decode($event['topics'], 1)
	);
}

die(json_encode($results));