<?php
/**
* 
*/
class Date
{
	public $CURRENT_DATETIME,$CURRENT_TIME,$CURRENT_DATE ;
	private $timeDateFormat,$timeFormat,$dateformat;

	function __construct($time_format=12,$dateformat="Y-m-d")
	{
		$this->dateformat = $dateformat;
		if((int)$time_format===24){
			$this->timeDateFormat = $dateformat." H:i:s";
			$this->timeFormat = "H:i:s";
		}else{
			$this->timeDateFormat = $dateformat." h:i A";
			$this->timeFormat = "h:i A";
		}
		
		$this->CURRENT_DATETIME = date($this->timeDateFormat);
		$this->CURRENT_TIME = date($this->timeFormat);
		$this->CURRENT_DATE = date($this->dateformat);
	}

	public function getDate()
	{
		return $this->CURRENT_DATE;
	}

	public function getTime()
	{
		return $this->CURRENT_TIME;
	}

	public function getDatetime()
	{
		return $this->CURRENT_DATETIME;
	}

	public function isValidDate($date='')
	{
		$validedate = $this->relative_time($this->format_date($date));
		$pass = ($validedate === "Invalide date")?false:true;
		if($pass){
			$checkdate = date_parse($this->format_date($date));
			if ($checkdate["error_count"] == 0 && checkdate($checkdate["month"], $checkdate["day"], $checkdate["year"])){
				return true;
			}else{
				return false;
			}
       }   
	}

	public function getFurtureDate($num,$which='',$current_datetime=null)
	{
		if ($current_datetime) {
			$this->CURRENT_DATETIME=$current_datetime;
		}
		
		$furturedate = null;
		$num = (int)$num;
		switch ($which) {

			case 'minutes': case 'min': case 'mns': case 'mins':
				$minutes = '+'.$num.' minutes';
				$furturedate = date($this->timeDateFormat, strtotime($minutes, strtotime($this->CURRENT_DATETIME)));
				break;
			
			case 'days': case 'day': case  'dys': case  'dayz': case  'dy': case  'dyz':
				$num = $num*24;
				$hours = '+'.$num.' hours';
				$furturedate = date($this->timeDateFormat, strtotime($hours, strtotime($this->CURRENT_DATETIME)));
				break;
			
			case 'weeks': case 'week': case 'wek': case 'wk':
				$week = '+'.$num.' week';
				$furturedate = date($this->timeDateFormat, strtotime($week,strtotime($this->CURRENT_DATETIME)));
				break;

			case 'months': case 'month': case 'mth': case 'mont':
				$month = '+'.$num.' month';
				$furturedate = date($this->timeDateFormat, strtotime($month,strtotime($this->CURRENT_DATETIME)));
				break;

			case 'years': case 'yrs': case 'year': case 'yer': case 'yearz':
				$year = '+'.$num.' year';
				$furturedate = date($this->timeDateFormat, strtotime($year,strtotime($this->CURRENT_DATETIME)));
				break;
			
			default:
				$hours = '+'.$num.' hours';
				$furturedate = date($this->timeDateFormat, strtotime($hours,strtotime($this->CURRENT_DATETIME)));
				break;
		}
		
		return $furturedate;
	}

	public function getPassedDate($num,$which='',$current_datetime=null)
	{
		if ($current_datetime) {
			$this->CURRENT_DATETIME=$current_datetime;
		}

		$passedDate = null;
		$num = (int)$num;
		switch ($which) {

			case 'minutes': case  'min': case  'mns': case  'mins':
				$minutes = '-'.$num.' minutes';
				$passedDate = date($this->timeDateFormat, strtotime($minutes, strtotime($this->CURRENT_DATETIME)));
				break;
			
			case 'days': case  'day': case  'dys': case  'dayz': case  'dy': case  'dyz':
				$num = $num*24;
				$hours = '-'.$num.' hours';
				$passedDate = date($this->timeDateFormat, strtotime($hours, strtotime($this->CURRENT_DATETIME)));
				break;
			
			case 'weeks': case  'week': case  'wek': case  'wk':
				$week = '-'.$num.' week';
				$passedDate = date($this->timeDateFormat, strtotime($week,strtotime($this->CURRENT_DATETIME)));
				break;

			case 'months': case  'month': case  'mth': case  'mont':
				$month = '-'.$num.' month';
				$passedDate = date($this->timeDateFormat, strtotime($month,strtotime($this->CURRENT_DATETIME)));
				break;

			case 'years': case  'yrs': case  'year': case  'yer': case  'yearz':
				$year = '-'.$num.' year';
				$passedDate = date($this->timeDateFormat, strtotime($year,strtotime($this->CURRENT_DATETIME)));
				break;
			
			default:
				$hours = '-'.$num.' hours';
				$passedDate = date($this->timeDateFormat, strtotime($hours,strtotime($this->CURRENT_DATETIME)));
				break;
		}
		
		return $passedDate;
	}

	public function format_full_datetime($value)
	{
		return date("r", strtotime($value));
	}

	public function format_datetime($value)
	{
		return date($this->timeDateFormat, strtotime($value));
	}

	public function sql_datetime($value)
	{
		return date("Y-m-d H:i:s", strtotime($value));
	}

	public function sql_date($value)
	{
		return date("Y-m-d", strtotime($value));
	}
	
	public function format_time($value)
	{
		return date($this->timeFormat, strtotime($value));
	}

	public function format_date($value)
	{
		return date($this->dateformat, strtotime($value));
	}

	public function format_datetime_short($value)
	{
		return date("D. jS M. y. ".$this->timeFormat, strtotime($value));
	}

	/**
     * Parse Date Or Timestamp Object into Relative Time (e.g. 2 days Ago, 2 days from now)
     * @return  string
     */
	public function relative_time($date){
		if(empty($date)) {
			return "No date provided";
		}
		
		$periods         = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
		$lengths         = array("60","60","24","7","4.35","12","10");
		
		$now             = time();
		
		//check if supplied Date is in unix date form
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		
		
		   // check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}

		// is it future date or past date
		if($now > $unix_date) {    
			$difference     = $now - $unix_date;
			$tense         = "ago";
			
		} else {
			$difference     = $unix_date - $now;
			$tense         = "from now";
		}
		
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}
		
		$difference = round($difference);
		
		if($difference != 1) {
			$periods[$j].= "s";
		}
		
		return "$difference $periods[$j] {$tense}";
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function human_date($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("l jS F, Y. ", $unix_date);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function _human_date($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("jS F, Y. ", $unix_date);
	}
	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function human_datetime($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("l jS F, Y. h:i:s A", $unix_date);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function music_date($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("Y", $unix_date);
	}

	public function getDaysBetweenTime($start,$end){
		$diff =  $end - $start;
		return round($diff / 86400);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function month_year($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("M Y ", $unix_date);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function month_year_day($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("d M, Y", $unix_date);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function day($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("jS", $unix_date);
	}

	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function human_date_short($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("D. jS M. y. ", $unix_date);
	}
	/**
     * Parse Date Or Timestamp Object into Human Readable Date (e.g. 26th of March 2016)
     * @return  string
     */
	public function human_datetime_short($date){
		if(empty($date)) {
			return "No date provided";
		}
		if(is_numeric($date)){
			$unix_date        = $date;
		}
		else{
			$unix_date         = strtotime($date);
		}
		// check validity of date
		if(empty($unix_date)) {    
			return "Invalide date";
		}
		return date("D. jS M. Y. h:i:s A", $unix_date);
	}
}
?>