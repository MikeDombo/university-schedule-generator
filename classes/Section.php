<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

This class is a child of Course and includes important data as well as functions to determine if
this Section conflicts with a different section.  In addition, this class has helpful variables like
earliestTime, latestTime, and meetsFriday that store the first day and time a class meets, the 
last day and time a class meets, and if the class meets on Fridays
**/

class Section extends Course{
	/** @var array $earliestTime earliest time a class meets, no matter what day that meeting occurs */
	private $earliestTime;
	/** @var  array $latestTime latest time a class meets, no matter what day that meeting occurs */
	private $latestTime;
	/** @var bool $meetsFriday true if the class has a meeting on Friday */
	private $meetsFriday;
	/** @var array $meetingTime stores all times and days a section meets */
	public $meetingTime;
	/** @var array $lastTime absolute last time the class meets in a week */
	private $lastTime;
	/** @var array $firstTime absolute first time the class meets in a week */
	private $firstTime;
	/** @var array stores all registration numbers used in the section, ie. the main CRN and the CRN for a lab section */
	private $crn = [];
	/** @var bool true if there are multiple options of lab or drill sections for each lecture section */
	private $multiple = false;
	/** @var String $prof Name of the professor teaching the section */
	private $prof;
	/** @var bool $preregistered true if this Section was preregistered */
	public $preregistered = false;

	/**
	 * Section constructor.
	 * Constructor for Section that includes all the necessary parameters for the Course constructor as well as the CRN(s)
	 *
	 * @param String $courseTitle
	 * @param String $fos
	 * @param String $courseNum
	 * @param Integer $units
	 * @param array|String $crn
	 */
	public function __construct($courseTitle, $fos, $courseNum, $units, $crn){
		parent::__construct($courseTitle, $fos, $courseNum, $units);
		$this->meetsFriday = false;
		foreach($crn as $v){ //iterate through all CRN(s) and push each to the crn array
			array_push($this->crn, $v);
		}		
	}
	
	/**
	 * Add additional CRN to the CRN array
	 * @param String $crn
	**/
	public function addCRN($crn){
		array_push($this->crn, $crn);
	}
	

	/**
	 * Add a single day/time to a Section
	 *
	 * In addition to adding a day and time to $meetingTime, it also updates $meetsFriday, $earliestTime, $latestTime,
	 * and $lastTime as appropriate
	 * @param String $day day of the week
	 * @param String $from time the class starts
	 * @param String $to time the class ends
	 */
	public function addTime($day, $from, $to){
		$day = $this->dayToInt($day); //convert day to int in order to convert whatever format $day is in, into the globally recognized int version
		$day = $this->intToDay($day); //convert day from int to string that is recognized easily and human readable
		if(isset($this->meetingTime[$day])){ //if the day is existing in $meetingTime, add the time to that day
			array_push($this->meetingTime[$day], ["from"=>strtotime($from),  "to"=>strtotime($to)]);
		}
		else{ //the day does not exist already in $meetingTime
			$temp = array();
			array_push($temp, ["from"=>strtotime($from),  "to"=>strtotime($to)]);
			$this->meetingTime[$day] = $temp;
		}
		
		//update $earliestTime, $latestTime, and $lastTime
		if(!isset($this->earliestTime)){//if earliestTime has not been set yet, just set it to the current value
			$this->earliestTime = [$this->dayToInt($day), strtotime($from)];
		}
		else if($this->earliestTime[1] > strtotime($from)){//if the preexisting time is later than the new time, set $earliestTime to the new day and time
			$this->earliestTime = [$this->dayToInt($day), strtotime($from)];
		}

		if(!isset($this->latestTime)){//if latestTime has not been set yet, just set it to the current value
			$this->latestTime = [$this->dayToInt($day), strtotime($from)];
		}
		else if($this->latestTime[1] < strtotime($from)){//if the preexisting time is earlier than the new time, set $earliestTime to the new day and time
			$this->latestTime = [$this->dayToInt($day), strtotime($from)];
		}
		
		if(!isset($this->lastTime)){//if lastTime has not been set yet, just set it to the current value
			$this->lastTime = [$this->dayToInt($day), strtotime($to)];
		}
		else if($this->lastTime[0] < $this->dayToInt($day)){//if the current day in lastTime is earlier in the week than the new day, then update latestTime to the new day and time
			$this->lastTime = [$this->dayToInt($day), strtotime($to)];
		}
		else if($this->lastTime[0] == $this->dayToInt($day) && $this->lastTime[1] < strtotime($to)){//if the current day in lastTime is the same as the new day and the current time is earlier than the new time, update lastTime
			$this->lastTime = [$this->dayToInt($day), strtotime($to)];
		}

		if(!isset($this->firstTime)){//if firstTime has not been set yet, just set it to the current value
			$this->firstTime = [$this->dayToInt($day), strtotime($from)];
		}
		else if($this->firstTime[0] > $this->dayToInt($day)){//if the current day in firstTime is later in the week
			// than the new day, then update firstTime to the new day and time
			$this->firstTime = [$this->dayToInt($day), strtotime($from)];
		}
		else if($this->firstTime[0] == $this->dayToInt($day) && $this->firstTime[1] < strtotime($from)){//if the current
			// day in firstTime is the same as the new day and the current time is earlier than the new time, update lastTime
			$this->firstTime = [$this->dayToInt($day), strtotime($from)];
		}
		
		if($day == "Friday"){
			$this->meetsFriday = true;
		}
	}
	
	/**
	 * returns boolean, true if $other conflicts with this section in time or in course (both sections are the same
	 * course)
	 * @param Section $other
	 * @return boolean true if a conflict exists, false otherwise
	**/
	public function conflictsWith($other){
		if($this->getFieldOfStudy() == $other->getFieldOfStudy() && $this->getCourseNumber() == $other->getCourseNumber()){ //check if the fields of study and course numbers are the same
			if($this->getCourseTitle() == $other->getCourseTitle()){//if the field of study is FYS and the course titles and the same, then the sections must conflict
				return true;
			}
			else if($this->preregistered || $other->preregistered){
				return true;
			}
		}
		
		//Sections do not conflict in course, so we must now check if they conflict in time
		if(!isset($this->meetingTime)){//check that section has times
			return false;
		}
		return $this->timeConflict($other);
	}
	
	/**
	 * Does the same as conflictsWith, except it only checks for overlapping times, not course characteristics
	 * @param Section $other
	 * @return boolean true if a a time conflict exists or there are multiple sections
	**/
	public function conflictsWithTime($other){
		if($this->multiple){
			return true;
		}
		return $this->timeConflict($other);
	}

	/**
	 * Returns true if a time conflict exists between this Section and the $other section.
	 * @param Section $other
	 * @return bool
	 */
	private function timeConflict($other){
		foreach($this->meetingTime as $k=>$a){ //iterate through each day in meetingTime
			if(isset($other->meetingTime[$k])){ //if $other also has the same day in its meetingTime, then continue
				foreach($this->meetingTime[$k] as $k2=>$v){ //iterate through each block of time for the current day in this section
					foreach($other->meetingTime[$k] as $i=>$v2){ //iterate through each block of time for the current day in the other section
						if($v["from"]<=$other->meetingTime[$k][$i]["to"] && $v["to"]>=$other->meetingTime[$k][$i]["from"]){
							return true;
						}
					}
				}
			}
		}
		return false;
	}
	
	
	/*
	General accessor methods
	*/
	public function setMultiples($a){
		$this->multiple = $a;
	}
	
	public function setProf($a){
		$this->prof = $a;
	}
	
	public function getEarliestTime(){
		return $this->earliestTime;
	}
	
	public function getLatestTime(){
		return $this->latestTime;
	}
	
	public function meetsFriday(){
		return $this->meetsFriday;
	}
	
	public function getLastTime(){
		return $this->lastTime;
	}

	public function getFirstTime(){
		return $this->firstTime;
	}
	
	public function getCRN(){
		return $this->crn;
	}
	
	public function getProf(){
		return $this->prof;
	}
	
	public function __toString(){
		$me = $this->getCourseTitle()." on ".$this->intToDay($this->getEarliestTime()[0])." at ".date("g:i A", $this->getEarliestTime()[1]);
		return $me;
	}	
	
	private function dayToInt($day){
		switch($day){
			case "Monday":				
				return 0;
			case "Tuesday":				
				return 1;
			case "Wednesday":				
				return 2;
			case "Thursday":				
				return 3;
			case "Friday":				
				return 4;
			case "Saturday":				
				return 5;
			case "Sunday":				
				return 6;
			case "M":
				return 0;
			case "T":
				return 1;
			case "W":
				return 2;
			case "R":
				return 3;
			case "F":
				return 4;
			case "S":
				return 5;
			case "Su":
				return 6;
		}
	}	
	
	private function intToDay($d){
		switch($d){
			case 0:
				return "Monday";
			case 1:
				return "Tuesday";
			case 2:
				return "Wednesday";
			case 3:
				return "Thursday";
			case 4:	
				return "Friday";
			case 5:
				return "Saturday";
			case 6:
				return "Sunday";
		}
	}
}
