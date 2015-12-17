<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

This class essentially holds many sections that represent a single schedule option.  It includes variables for ease of use like,
$numberOfClasses, $numberOfUnits, $earliestTime, $latestTime, $firstTime, $lastTime, $fridayFree, and $score.
**/

class Schedule{
	private $listOfSections;
	private $numberOfClasses;
	private $numberOfUnits;
	private $earliestTime;
	private $latestTime;
	private $firstTime;
	private $lastTime;
	private $fridayFree;
	public $score;
	
	/**
	Default constructor to initialize critical values
	**/
	public function __construct(){
		$this->listOfSections = array();
		$this->numberOfClasses = 0;
		$this->numberOfUnits = 0;
		$this->fridayFree = true;
		$this->score = 0;
	}
	
	/**
	adds a single section to the list of sections and updates important variables, much like addTime in the Section class
	**/
	public function addSection($sec){
		array_push($this->listOfSections, $sec); //add the Section to the current list
		$this->numberOfClasses += 1;
		$this->numberOfUnits += $sec->getNumUnits();
		
		if(!isset($this->earliestTime)){
			$this->earliestTime = $sec->getEarliestTime();
		}
		else if($this->earliestTime[1] > $sec->getEarliestTime()[1]){
			$this->earliestTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
		}
		
		if(!isset($this->latestTime)){
			$this->latestTime = $sec->getLatestTime();
		}
		else if($this->latestTime[1] < $sec->getLatestTime()[1]){
			$this->latestTime = array($sec->getLatestTime()[0], $sec->getLatestTime()[1]);
		}
		
		if(!isset($this->firstTime)){
			$this->firstTime = $sec->getEarliestTime();
		}
		else if($this->firstTime[0] > $sec->getEarliestTime()[0]){
			$this->firstTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
		}
		else if($this->firstTime[0] == $sec->getEarliestTime()[0] && $this->firstTime[1] > $sec->getEarliestTime()[1]){
			$this->firstTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
		}
		
		if(!isset($this->lastTime)){
			$this->lastTime = $sec->getLastTime();
		}
		else if($this->lastTime[0] < $sec->getLastTime()[0]){
			$this->lastTime = array($sec->getLastTime()[0], $sec->getLastTime()[1]);
		}
		else if($this->lastTime[0] == $sec->getLastTime()[0] && $this->lastTime[1] < $sec->getLastTime()[1]){
			$this->lastTime = array($sec->getLastTime()[0], $sec->getLastTime()[1]);
		}
		
		if($sec->meetsFriday()){
			$this->fridayFree = false;
		}
	}
	
	public function setScore(){
		$classes = $this->numberOfUnits+$this->numberOfClasses;
		$this->score = $classes*2;
		$this->score += ($this->numberOfClasses - reset($this->getCPD()))*1.5;
		if($this->fridayFree){
			$this->score += 4;
		}
		
		$earliest = array();
		foreach($this->listOfSections as $k=>$v){
			if(isset($v->meetingTime)){
				foreach($v->meetingTime as $day=>$times){
					foreach($times as $k2=>$time){
						if(isset($earliest[$day])){
							if($earliest[$day] > $time["from"]){
								$earliest[$day] = $time["from"];
							}
						}
						else{
							$earliest[$day] = array();
							$earliest[$day] = $time["from"];
						}
					}
				}
			}
		}
		
		$timeScore = 0;
		foreach($earliest as $k=>$v){
			$timeScore += ($v - strtotime("10:00 AM"))/3600;
		}
		
		$this->score += ($timeScore/$this->numberOfClasses)*.1;
	}
	
	public function getSchedule(){
		return $this->listOfSections;
	}
	
	public function getCPD(){
		$arr = array();
		$arr2 = array();
		foreach($this->listOfSections as $v){
			if(isset($v->meetingTime)){
				foreach($v->meetingTime as $k=>$m){
					if(!isset($arr[$k])){
						$arr[$k] = 1;
					}
					else{
							$arr[$k] +=1;
					}
				}
			}
		}
		
		arsort($arr);
		$i = reset($arr);
		foreach($arr as $k=>$v){
			if($i == $v){
				$arr2[$this->dayToInt($k)]=$v;
			}
		}
		
		ksort($arr2);
		foreach($arr2 as $k=>$v){
			unset($arr2[$k]);
			$arr2[$this->intToDay($k)] = $v;
		}
		return $arr2;
	}	
	
	//Generic Accessor Methods
	public function getNumClasses(){
        return $this->numberOfClasses;
    }
    
    public function getEarliestTime(){
        return $this->earliestTime;
    }
    
    public function getlatestTime(){
        return $this->latestTime;
    }
    
    public function fridayFree(){
        return $this->fridayFree;
    }
	
	public function getNumUnits(){
        return $this->numberOfUnits;
    }
	
	public function getLastTime(){
		return $this->lastTime;
	}
	
	public function getFirstTime(){
		return $this->firstTime;
	}
	
	public function dayToInt($day){
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
		}
	}
	
	public function intToDay($d){
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
?>