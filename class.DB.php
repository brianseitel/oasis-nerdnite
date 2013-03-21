<?

function pp($data) {
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

function pd($data) {
	pp($data);
	die();
}

class DB {
	
	protected $db;

	public static function connect() {
		return new mysqli("127.0.0.1", "root", "", "oasis_nerdnite");
	}

	public static function clean_string($string) {
		$db = self::connect();
		return $db->real_escape_string($string);
	}

	public static function update($sql) {
		$db = self::connect();
		$result = $db->query($sql);
		return $result;
	}

	public static function getArray($sql) {
		$array = array();
		$db = self::connect();
		$results = $db->query($sql);
		while ($row = $results->fetch_assoc())
			$array[] = $row;
		return $array;
	}
}