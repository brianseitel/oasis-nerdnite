<!DOCTYPE html>
<html>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
</head>
<body>
<?
require('class.DB.php');

$data = DB::getArray('SELECT city, event_date, ticket_link, topics FROM events ORDER BY city');

foreach ($data as $event): ?>
	<h3><?= $event['city']; ?></h1>
	<? if (strtotime($event['event_date']) > 0): ?>
		<h5><?= date('F l d, Y @ ga', strtotime($event['event_date'])) ?></h3>
	<? endif ?>
	<? if ($event['topics']): ?>
		<ul>
			<? foreach (json_decode($event['topics'], 1) as $topic): ?>
				<li><?= $topic ?></li>
			<? endforeach ?>
		</ul>
	<? else: ?>
		<p>No topics found.</p>
	<? endif ?>
<? endforeach ?>
</body>
</html>