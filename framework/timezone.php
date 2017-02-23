<?php
namespace Framework;

class TimeZone extends Base {
	/**
	 * TimeZone converter - Converts the Time given in one zone to other zone,
	 * By default search for Organization Key set in session to get Organization Time Zone
	 * else searches for 'zone' key provided with the extra's array
	 * @param object $dt DateTime class object
	 * @param array $extra Keys => (org, zone)
	 * @return  object Object of class DateTime
	 */
	public static function zoneConverter($dt, $extra = []) {
	    $org = (Registry::get("session")->get("org")) ?? $extra['org'] ?? (object) [];
	    $zone = $extra['zone'] ?? $org->region['zone'] ?? 'Asia/Kolkata';
	    $tz = new \DateTimeZone($zone);
	    $newDt = new \DateTime(); $newDt->setTimezone($tz);

	    $newDt->setTimestamp($dt->getTimestamp());
	    return $newDt;
	}

	/**
	 * Generate UTC Date Time stamp based on the timezone provided to it
	 * so as to query for the whole day based on the given DateTime object
	 * @param string $date Date in string format -> Y-m-d
	 * @param object $dt object of class \DateTime
	 * @return array Array containing keys -> (start, end)
	 */
	public static function utcDateTime($date, $dt) {
	    $startDt = new \DateTime(); $endDt = new \DateTime();

	    $startDt->setTimezone($dt->getTimezone());
	    $endDt->setTimezone($dt->getTimezone());
	    
	    $start = (int) strtotime($date . ' 00:00:00');
	    $end = (int) strtotime($date . ' 23:59:59');
	    
	    $startDt->setTimestamp($start - $startDt->getOffset());
	    $endDt->setTimestamp($end - $endDt->getOffset());

	    $startTimeStamp = $startDt->getTimestamp() * 1000;
	    $endTimeStamp = $endDt->getTimestamp() * 1000 + 999;

	    return [
	        'start' => new \MongoDB\BSON\UTCDateTime($startTimeStamp),
	        'end' => new \MongoDB\BSON\UTCDateTime($endTimeStamp)
	    ];
	}

	public static function zoneTime($zone = 'Asia/Kolkata', $time = null) {
		if (is_null($time)) {
			$time = date('Y-m-d H:i:s');
		}
		$dt = new \DateTime();
		$dt = self::zoneConverter($dt, ['zone' => $zone]);
		$dt->modify($time);
		return $dt;
	}
}
