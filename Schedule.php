<?php
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
	
	public function __construct(){
		$this->listOfSections = array();
		$this->numberOfClasses = 0;
		$this->numberOfUnits = 0;
		$this->fridayFree = true;
		$this->score = 0;
	}
	
	public function addSection($sec){
		array_push($this->listOfSections, $sec);
		$this->numberOfClasses += 1;
		$this->numberOfUnits += $sec->getNumUnits();
		
		if(!isset($this->earliestTime)){
			$this->earliestTime = $sec->getEarliestTime();
		}
		if($this->earliestTime[1] > $sec->getEarliestTime()[1]){
			$this->earliestTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
		}
		
		if(!isset($this->firstTime)){
			$this->firstTime = $sec->getEarliestTime();
		}
		else if($this->firstTime[0] > $sec->getEarliestTime()[0]){
			$this->firstTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
		}
		else if($this->firstTime[0] == $sec->getEarliestTime()[0]){
			if($this->firstTime[1] > $sec->getEarliestTime()[1]){
				$this->firstTime = array($sec->getEarliestTime()[0], $sec->getEarliestTime()[1]);
			}
		}
		
		if(!isset($this->lastTime)){
			$this->lastTime = $sec->getLastTime();
		}
		else if($this->lastTime[0] < $sec->getLastTime()[0]){
			$this->lastTime = array($sec->getLastTime()[0], $sec->getLastTime()[1]);
		}
		else if($this->lastTime[0] == $sec->getLastTime()[0]){
			if($this->lastTime[1] < $sec->getLastTime()[1]){
				$this->lastTime = array($sec->getLastTime()[0], $sec->getLastTime()[1]);
			}
		}
		
		if(!isset($this->latestTime)){
			$this->latestTime = $sec->getLatestTime();
		}
		if($this->latestTime[1] < $sec->getLatestTime()[1]){
			$this->latestTime = array($sec->getLatestTime()[0], $sec->getLatestTime()[1]);
		}
		
		if($sec->meetsFriday()){
			$this->fridayFree = false;
		}
	}
		
	public function getSchedule(){
		$arr = $this->listOfSections;
		usort($arr, function($a, $b){
				return (strtotime("+".($a->getEarliestTime()[0])." days", $a->getEarliestTime()[1]) < strtotime("+".($b->getEarliestTime()[0])." days", $b->getEarliestTime()[1])) ? -1 : 1;
		});
		return $arr;
	}
	
	private function compCPD($a, $b){
		
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
	
	public function getCourses(){
		$arr = array();
		foreach($this->listOfSections as $v){
			$tmp = ['title'=>$v->getCourseTitle(), 'fieldOfStudy'=>$v->getFieldOfStudy(), 'courseNum'=>$v->getCourseNumber(), 'time'=>date("g:i A", reset($v->meetingTime)['from']), 'day'=>key($v->meetingTime)];
			$arr[$v->getCourseTitle()] = array();
			array_push($arr[$v->getCourseTitle()], $tmp);
		}
		$arr['friday free'] = $this->fridayFree();
		$arr['Number of Courses'] = $this->getNumClasses();
		$arr['Earliest Time'] = date("g:i A", $this->earliestTime[1]);
		$arr['Latest Time'] = date("g:i A", $this->latestTime[1]);
		$arr['units']=$this->getNumUnits();
		return $arr;
	}
		
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
	
	public function getScore(){
		return $this->score;
	}
	
	public function __toString() {
        $me = "".$this->numberOfClasses.$this->fridayFree;
		foreach($this->listOfSections as $a){
			$me = $me.$a->getCourseTitle().$a->getLatestTime()[1].$a->getEarliestTime()[1].$a->getLatestTime()[0].$a->getEarliestTime()[0].$a->getCRN()[0];
		}
		return $me;
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