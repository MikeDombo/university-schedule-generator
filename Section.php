<?php
class Section extends Course{
	private $earliestTime;
	private $latestTime;
	private $meetsFriday;
	public $meetingTime;
	private $lastTime;
	private $crn = array();
	private $multiple = false;
	private $color = array(0, 0, 0);
	
	public function __construct($courseTitle, $fos, $courseNum, $units, $crn){
		parent::__construct($courseTitle, $fos, $courseNum, $units);
		$this->meetsFriday = false;
		foreach($crn as $v){
			array_push($this->crn, $v);
		}		
	}

	public function addCRN($crn){
		array_push($this->crn, $crn);
	}
	
	public function addTime($day, $from, $to){
		$day = $this->dayToInt($day);
		$day = $this->intToDay($day);
		if(isset($this->meetingTime[$day])){
			array_push($this->meetingTime[$day], ["from"=>strtotime($from),  "to"=>strtotime($to)]);
		}
		else{
			$temp = array();
			array_push($temp, ["from"=>strtotime($from),  "to"=>strtotime($to)]);
			$this->meetingTime[$day] = $temp;
		}
		if($day == "Friday"){
			$this->meetsFriday = true;
		}
		if(!isset($this->earliestTime)){
			$this->earliestTime = array($this->dayToInt($day), strtotime($from));
		}
		if($this->earliestTime[1] > strtotime($from)){
			$this->earliestTime = array($this->dayToInt($day), strtotime($from));
		}

		if(!isset($this->latestTime)){
			$this->latestTime = array($this->dayToInt($day), strtotime($from));
		}
		if($this->latestTime[1] < strtotime($from)){
			$this->latestTime = array($this->dayToInt($day), strtotime($from));
		}
		
		if(!isset($this->lastTime)){
			$this->lastTime = array($this->dayToInt($day), strtotime($to));
		}
		else if($this->lastTime[0] < $this->dayToInt($day)){
			$this->lastTime = array($this->dayToInt($day), strtotime($to));
		}
		else if($this->lastTime[0] == $this->dayToInt($day)){
			if($this->lastTime[1] < strtotime($to)){
				$this->lastTime = array($this->dayToInt($day), strtotime($to));
			}
		}
	}
	
	public function conflictsWith($other){
		if($this->getFieldOfStudy() == $other->getFieldOfStudy() && $this->getCourseNumber() == $other->getCourseNumber()){
			return true;
		}
		else{
			if(!isset($this->meetingTime)){
				return false;
			}
			foreach($this->meetingTime as $k=>$a){
				if(isset($other->meetingTime[$k])){
					foreach($this->meetingTime[$k] as $k2=>$v){
						foreach($other->meetingTime[$k] as $i=>$v2){
							if($v["from"]<=$other->meetingTime[$k][$i]["to"] && $v["to"]>=$other->meetingTime[$k][$i]["from"]){
								return true;
							}
						}
					}
				}
			}
		}
		return false;
	}
	
	public function conflictsWithTime($other){
		if($this->multiple){
			return true;
		}
		foreach($this->meetingTime as $k=>$a){
			if(isset($other->meetingTime[$k])){
				foreach($this->meetingTime[$k] as $k2=>$v){
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
	
	public function setMultiples($a){
		$this->multiple=$a;
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
	
	public function getCRN(){
		return $this->crn;
	}
	
	public function getColor(){
		return $this->color;
	}
	
	public function setColor($a){
		$this->color = $a;
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
?>