<?php

/**
 * Authored by Michael Dombrowski, http://mikedombrowski.com
 * Original repository available at http://github.com/md100play/university-schedule-generator
 *
 * This class essentially holds many sections that represent a single schedule option.  It includes variables for ease
 * of use like,
 * $numberOfClasses, $numberOfUnits, $earliestTime, $latestTime, $firstTime, $lastTime, $fridayFree, and $score.
 **/
class Schedule {
	/** @var array|Section */
	private $listOfSections;
	/** @var int $numberOfClasses */
	private $numberOfClasses;
	/** @var int $numberOfUnits */
	private $numberOfUnits;
	private $earliestTime;
	private $latestTime;
	private $firstTime;
	private $lastTime;
	/** @var bool $fridayFree */
	private $fridayFree;
	/** @var int $score */
	public $score;

	/**
	 * Default constructor to initialize critical values
	 **/
	public function __construct(){
		$this->listOfSections = [];
		$this->numberOfClasses = 0;
		$this->numberOfUnits = 0;
		$this->fridayFree = true;
		$this->score = 0;
	}

	/**
	 * Adds a single section to the list of sections and updates important variables, much like addTime in the Section
	 * class
	 *
	 * @param Section $sec
	 **/
	public function addSection(Section $sec){
		$this->listOfSections[] = $sec; //add the Section to the current list
		$this->numberOfClasses += 1;
		$this->numberOfUnits += $sec->getNumUnits();

		if($this->fridayFree && $sec->meetsFriday()){
			$this->fridayFree = false;
		}
	}

	public function generateFirstLastTimes(){
		foreach($this->listOfSections as $sec){
			//if there is no earliest time, just set it
			if(!isset($this->earliestTime)){
				$this->earliestTime = $sec->getEarliestTime();
			}
			//if the current set time is later than the earliest time of the new section, change earliestTime
			else if($this->earliestTime[1] > $sec->getEarliestTime()[1]){
				$this->earliestTime = [$sec->getEarliestTime()[0], $sec->getEarliestTime()[1]];
			}

			//if the latest time isn't set, just set it
			if(!isset($this->latestTime)){
				$this->latestTime = $sec->getLatestTime();
			}
			//if the current latest time is earlier than the latest time of the new section, change latestTime
			else if($this->latestTime[1] < $sec->getLatestTime()[1]){
				$this->latestTime = [$sec->getLatestTime()[0], $sec->getLatestTime()[1]];
			}

			//if the first time isn't set, then set it
			if(!isset($this->firstTime)){
				$this->firstTime = $sec->getFirstTime();
			}
			//if the current first time's day is later than the earliest time's day, then set the first time
			else if($this->firstTime[0] > $sec->getFirstTime()[0]){
				$this->firstTime = [$sec->getFirstTime()[0], $sec->getFirstTime()[1]];
			}
			//if the current first time's day is the same as the earliest time's day, and the time of firstTime is later than earliestTime, then set it
			else if($this->firstTime[0] == $sec->getFirstTime()[0] && $this->firstTime[1] > $sec->getFirstTime()[1]){
				$this->firstTime = [$sec->getFirstTime()[0], $sec->getFirstTime()[1]];
			}

			//if the last time isn't set, then set it
			if(!isset($this->lastTime)){
				$this->lastTime = $sec->getLastTime();
			}
			//if the current last time's day is earlier than the last time's day, then set the last time
			else if($this->lastTime[0] < $sec->getLastTime()[0]){
				$this->lastTime = [$sec->getLastTime()[0], $sec->getLastTime()[1]];
			}
			//if the current last time's day is the same as the latest time's day, and the time of lastTime is earlier than latestTime, then set it
			else if($this->lastTime[0] == $sec->getLastTime()[0] && $this->lastTime[1] < $sec->getLastTime()[1]){
				$this->lastTime = [$sec->getLastTime()[0], $sec->getLastTime()[1]];
			}
		}
	}

	/**
	 * This function sets the score variable that is used to sort all schedules based on number of classes, number of
	 * units, classes per day, and the earliest time classes occur
	 *
	 * @param bool $morning
	 **/
	public function setScore($morning){
		$classes = $this->numberOfUnits + $this->numberOfClasses;
		$this->score = $classes * 2;//set score to number of classes and units, then scale by a factor of 2
		$cpd = $this->getCPD();
		$this->score += ($this->numberOfClasses - reset($cpd)) * 1.5;//add then number of classes minus the greatest number of classes in a day and scale by 1.5
		if($this->fridayFree){
			$this->score += 2;
		}

		//This section calculates the score to add due to classes being later than 10:00am
		$earliest = [];
		foreach($this->listOfSections as $k => $v){
			if($v->preregistered){
				$this->score += .25;
			}
			if(isset($v->meetingTime)){
				foreach($v->meetingTime as $day => $times){
					foreach($times as $time){
						if(isset($earliest[$day])){
							if($earliest[$day] > $time["from"]){
								$earliest[$day] = $time["from"];
							}
						}
						else{
							$earliest[$day] = [];
							$earliest[$day] = $time["from"];
						}
					}
				}
			}
		}

		$timeScore = 0;
		foreach($earliest as $k => $v){
			$timeScore += ($v - strtotime("10:00 AM")) / 3600;
		}
		if($morning){
			//scale the timeScore by the number of classes and then only add 10% of that because it isn't very important
			$this->score -= ($timeScore / $this->numberOfClasses) * .1;
		}
		else{
			//scale the timeScore by the number of classes and then only add 10% of that because it isn't very important
			$this->score += ($timeScore / $this->numberOfClasses) * .1;
		}
	}

	/**
	 * This function calculates the number of classes on each day
	 **/
	public function getCPD(){
		$arr = [];
		$arr2 = [];
		foreach($this->listOfSections as $v){
			if(isset($v->meetingTime)){
				foreach(array_keys($v->meetingTime) as $k){
					if(!isset($arr[$k])){
						$arr[$k] = 1;
					}
					else{
						$arr[$k] += 1;
					}
				}
			}
		}

		arsort($arr);
		$i = reset($arr);
		foreach($arr as $k => $v){
			if($i == $v){
				$arr2[$this->dayToInt($k)] = $v;
			}
		}

		ksort($arr2);
		foreach($arr2 as $k => $v){
			unset($arr2[$k]);
			$arr2[$this->intToDay($k)] = $v;
		}

		return $arr2;
	}

	/**
	 * @return array|Section
	 */
	public function getSchedule(){
		return $this->listOfSections;
	}

	/**
	 * @return int
	 */
	public function getNumClasses(){
		return $this->numberOfClasses;
	}

	public function getEarliestTime(){
		return $this->earliestTime;
	}

	public function getlatestTime(){
		return $this->latestTime;
	}

	/**
	 * @return bool
	 */
	public function fridayFree(){
		return $this->fridayFree;
	}

	/**
	 * @return int
	 */
	public function getNumUnits(){
		return $this->numberOfUnits;
	}

	public function getLastTime(){
		return $this->lastTime;
	}

	public function getFirstTime(){
		return $this->firstTime;
	}

	/**
	 * @param string $day
	 * @return int
	 */
	public static function dayToInt($day){
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

	/**
	 * @param int $d
	 * @return string
	 */
	public static function intToDay($d){
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
