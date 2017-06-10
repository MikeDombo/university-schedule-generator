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
		//iterate through all CRN(s) and push each to the crn array
		foreach($crn as $v){
			$this->addCRN($v);
		}		
	}
	
	/**
	 * Add additional CRN to the CRN array
	 * @param String $crn
	**/
	public function addCRN($crn){
		$this->crn[] = $crn;
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
		$dayInt = Schedule::dayToInt($day); //convert day to int in order to convert whatever format $day is in, into
		// the globally recognized int version
		$dayDay = Schedule::intToDay($dayInt); //convert day from int to string that is recognized easily and human
		// readable
		if(isset($this->meetingTime[$dayDay])){ //if the day is existing in $meetingTime, add the time to that day
			$this->meetingTime[$dayDay][] = ["from"=>strtotime($from),  "to"=>strtotime($to)];
		}
		else{ //the day does not exist already in $meetingTime
			$temp = [];
			$temp[] = ["from"=>strtotime($from),  "to"=>strtotime($to)];
			$this->meetingTime[$dayDay] = $temp;
		}
		
		//update $earliestTime, $latestTime, and $lastTime

		//if earliestTime has not been set yet, just set it to the current value
		if(!isset($this->earliestTime)){
			$this->earliestTime = [$dayInt, strtotime($from)];
		}
		//if the preexisting time is later than the new time, set $earliestTime to the new day and time
		else if($this->earliestTime[1] > strtotime($from)){
			$this->earliestTime = [$dayInt, strtotime($from)];
		}

		//if latestTime has not been set yet, just set it to the current value
		if(!isset($this->latestTime)){
			$this->latestTime = [$dayInt, strtotime($to)];
		}
		//if the preexisting time is earlier than the new time, set $latestTime to the new day and time
		else if($this->latestTime[1] < strtotime($to)){
			$this->latestTime = [$dayInt, strtotime($to)];
		}

		//if lastTime has not been set yet, just set it to the current value
		if(!isset($this->lastTime)){
			$this->lastTime = [$dayInt, strtotime($to)];
		}
		//if the current day in lastTime is earlier in the week than the new day, then update latestTime to the new day and time
		else if($this->lastTime[0] < $dayInt){
			$this->lastTime = [$dayInt, strtotime($to)];
		}
		//if the current day in lastTime is the same as the new day and the current time is earlier than the new time, update lastTime
		else if($this->lastTime[0] == $dayInt && $this->lastTime[1] < strtotime($to)){
			$this->lastTime = [$dayInt, strtotime($to)];
		}

		//if firstTime has not been set yet, just set it to the current value
		if(!isset($this->firstTime)){
			$this->firstTime = [$dayInt, strtotime($from)];
		}
		//if the current day in firstTime is later in the week
		else if($this->firstTime[0] > $dayInt){
			// than the new day, then update firstTime to the new day and time
			$this->firstTime = [$dayInt, strtotime($from)];
		}
		//if the current day in firstTime is the same as the new day and the current time is earlier than the new
		//time, update lastTime
		else if($this->firstTime[0] == $dayInt && $this->firstTime[1] < strtotime($from)){
			$this->firstTime = [$dayInt, strtotime($from)];
		}
		
		if($dayDay == "Friday"){
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
		//check if the fields of study and course numbers are the same
		if($this->getFieldOfStudy() == $other->getFieldOfStudy() && $this->getCourseNumber() == $other->getCourseNumber()){
			//if the field of study is FYS and the course titles and the same, then the sections must conflict
			if($this->getCourseTitle() == $other->getCourseTitle() || $this->preregistered || $other->preregistered){
				return true;
			}
		}
		
		// Sections do not conflict in course, so we must now check if they conflict in time
		// check that section has times
		if(!isset($this->meetingTime)){
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
		//iterate through each day in meetingTime
		foreach($this->meetingTime as $k=>$a){
			//if $other also has the same day in its meetingTime, then continue
			if(isset($other->meetingTime[$k])){
				//iterate through each block of time for the current day in this section
				foreach($this->meetingTime[$k] as $v){
					//iterate through each block of time for the current day in the other section
					foreach($other->meetingTime[$k] as $i=>$v2){
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
		return $this->getCourseTitle()." on ".Schedule::intToDay($this->getEarliestTime()[0])." at ".date("g:i A", $this->getEarliestTime()[1]);
	}

}
