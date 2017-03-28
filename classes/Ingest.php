<?php

class Ingest{
	/** @var array|string $requestData Store the GET request */
	private $requestData;
	/** @var bool $morning */
	private $morning;
	/** @var array|mixed $courseInput */
	private $courseInput;
	/** @var mixed $preregistered */
	private $preregistered;
	/** @var bool $allowFull Allow full sections in generated schedules */
	private $allowFull;
	/** @var string $startTime */
	private $startTime;
	/** @var string $endTime */
	private $endTime;
	/** @var array|mixed $unwantedTimes */
	private $unwantedTimes;
	/** @var array|Section $allSections */
	private $allSections = [];
	/** @var int $requiredCourseNum */
	private $requiredCourseNum = 0;
	/** @var \PDO $link */
	private $link;
	/** @var int $classCount */
	private $classCount;
	/** @var array $daysWithUnwantedTimes */
	private $daysWithUnwantedTimes = [];


	/**
	 * Ingest constructor.
	 *
	 * @param string $data
	 * @param PDO $pdo
	 */
	public function __construct($pdo, $data){
		$this->requestData = json_decode($data, true);
		$this->courseInput = $this->requestData["allCourses"];
		$this->preregistered = $this->requestData["preregistered"];
		$this->morning = $this->requestData["timePref"];
		$this->allowFull = $this->requestData["fullClasses"];
		$this->startTime = strtotime($this->requestData["startTime"]);
		$this->endTime = strtotime($this->requestData["endTime"]);
		$this->unwantedTimes = $this->requestData["unwantedTimes"];
		$this->link = $pdo;
	}

	public function getAllSections(){
		return $this->allSections;
	}

	public function getClassCount(){
		return $this->classCount;
	}

	public function getMorning(){
		return $this->morning;
	}

	public function getRequiredCourseNum(){
		return $this->requiredCourseNum;
	}

	/**
	 * Generates a random RGB array
	 *
	 * @param array|int $c array of 3 offsets
	 * @return array|int
	 */
	private function generateColor($c){
		$red = rand(0, 255);
		$green = rand(0, 255);
		$blue = rand(0, 255);
		$red = ($red + $c[0]) / 2;
		$green = ($green + $c[1]) / 2;
		$blue = ($blue + $c[2]) / 2;

		return [intval($red), intval($green), intval($blue)];
	}

	/**
	 * Makes a new \Section using the data provided in $rows from the database
	 *
	 * @param array $section
	 * @param string $title
	 * @param array $rows
	 * @return \Section
	 */
	private function makeNewSection($section, $title, $rows){
		$tempSec = new Section($title, $rows["SUBJ"], $rows["CRSE"], floatval($rows["UNITS"]), [$rows["CRN"]]);
		if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
			$tempSec->setRequiredCourse(true);
		}
		foreach($rows as $k=>$v){
			if($k == $v){
				$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
			}
		}
		$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
		if(!(array_search($rows["CRN"], $tempSec->getCRN()) > -1)){
			$tempSec->addCRN($rows["CRN"]);
		}
		return $tempSec;
	}

	/**
	 * Generates the daysWithUnwatedTimes array which is an array of all the days which may have blacked out times
	 */
	private function setDaysWithUnwantedTimes(){
		if(isset($this->unwantedTimes)){
			$t = new Schedule();
			foreach($this->unwantedTimes as $k=>$v){
				foreach($v as $k2=>$v2){
					if(!in_array($k2, $this->daysWithUnwantedTimes)){
						$this->daysWithUnwantedTimes[] = $t->intToDay($t->dayToInt($k2));
					}
				}
			}
			unset($t);
		}
	}

	/**
	 * Generates the section variables for all preregistered classes and adds them to $allSections
	 * Also sets the class count variable
	 */
	private function generatePreregisteredSections(){
		$preregSections = [];
		foreach($this->preregistered as $k=>$v){
			$courseColor = $this->generateColor([255, 255, 255]);
			$crn = $v;

			$q = $this->link->prepare("SELECT * FROM `".DB_DATABASE_TABLE."` WHERE `CRN` = :crn");
			$q->bindValue(":crn", $crn, PDO::PARAM_INT);
			$q->execute();
			$result = $q->fetchAll(PDO::FETCH_ASSOC);

			foreach($result as $rows){
				if(!isset($rows["INSTR FN"])){
					$rows["INSTR FN"] = "";
				}
				if(!isset($rows["LASTNAME"])){
					$rows["LASTNAME"] = "";
				}

				$tempSec = new Section($rows["TITLE"], $rows["SUBJ"], $rows["CRSE"], floatval($rows["UNITS"]), [$rows["CRN"]]);

				foreach($rows as $k=>$v){
					if($k == $v){
						$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
					}
				}
				$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
				$tempSec->setColor($courseColor);
				$preregSections[] = $tempSec;
			}
		}

		// Try to merge preregistered sections if they are the same course (ex. class and a lab section)
		for($i=0; $i<count($preregSections); $i+=1){
			for($j=0; $j<count($preregSections); $j+=1){
				if($i == $j){
					continue;
				}
				$v = $preregSections[$i];
				$v2 = $preregSections[$j];
				if(similar_text($v->getCourseTitle(), $v2->getCourseTitle()) >= 10 && $v->getCourseNumber() == $v2->getCourseNumber() && $v->getFieldOfStudy() == $v2->getFieldOfStudy()){
					foreach($v2->getCRN() as $crn){
						if(!(array_search($crn, $v->getCRN()) > -1)){
							$v->addCRN($crn);
						}
					}
					foreach($v2->meetingTime as $day=>$times){
						foreach($times as $timeKey=>$time){
							$v->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
						}
					}
					unset($preregSections[$j]);
					$preregSections = array_values($preregSections);
				}
			}
		}

		foreach($preregSections as $v){
			$v->preregistered = true;
			$this->allSections[] = $v;
		}

		// Set the count of classes to know if a schedule has all the requested classes
		$this->classCount = count($this->courseInput)+count($preregSections);
	}

	/**
	 * Removes sections from the $allSections list if there is a problem with the time it meets
	 * ie. it is on a day, during a time that is blacked out, or it occurs outside of the start and end times chosen
	 */
	private function removeSectionsForTime(){
		$t = new Schedule();
		/** @var Section $section */
		foreach($this->allSections as $key=>$section){
			if($section->preregistered){
				continue;
			}
			if($this->startTime > $section->getEarliestTime()[1]|| $this->endTime < $section->getLatestTime()[1]){
				unset($this->allSections[$key]);
				continue;
			}
			foreach($section->meetingTime as $day=>$times){
				if(!in_array($day, $this->daysWithUnwantedTimes)){
					continue;
				}
				foreach($times as $days=>$time){
					foreach($this->unwantedTimes as $dayVal){
						foreach($dayVal as $val){
							if($t->intToDay($t->dayToInt($val)) != $days){
								continue;
							}
							else if(strtotime($dayVal["startTime"]) <= $time["to"] && strtotime($dayVal["endTime"]) >= $time["from"]){
								unset($this->allSections[$key]);
								continue 5;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Generates entries in the private variable $allSections
	 */
	public function generateSections(){
		$this->setDaysWithUnwantedTimes();

		foreach($this->courseInput as $key=>$section){
			if(!isset($section["FOS"]) || !isset($section["CourseNum"]) || !isset($section["Title"])){
				continue;
			}
			if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
				$this->requiredCourseNum++;
			}
			$subj = $section["FOS"];
			$num = $section["CourseNum"];
			$title = $section["Title"];
			$courseColor = $this->generateColor([255, 255, 255]);

			$q = $this->link->prepare("SELECT * FROM `".DB_DATABASE_TABLE."` WHERE `CRSE` = :num AND `SUBJ` = :subj");
			$q->bindValue(":num", $num, PDO::PARAM_INT);
			$q->bindValue(":subj", $subj, PDO::PARAM_STR);
			$q->execute();
			$result = $q->fetchAll(PDO::FETCH_ASSOC);

			$tempSection = [];
			$manyOptions = [];
			$multipleOptions = false;
			foreach($result as $rows){
				if(!$this->allowFull){
					if($rows["MAX"] <= $rows["ENRLLMNT"]){
						continue;
					}
				}
				if(($rows["SUBJ"] == "FYS" || $rows["SUBJ"] == "WELL" || strpos($title, "ST:") > -1 || strpos($title, "SP:") > -1 || ($rows["SUBJ"] == "HIST" && $rows["CRSE"] == "199") || ($rows["SUBJ"] == "BIOL" && $rows["CRSE"] == "199") || ($rows["SUBJ"] == "ENGL" && $rows["CRSE"] == "299")) && $rows["TITLE"] != $title){
					if(!(strpos($rows["TITLE"], " LAB") > -1 || strpos($title, " LAB") > -1)){
						continue;
					}
				}
				if(isset($section["displayTitle"])){
					$title = $section["displayTitle"];
				}
				$sectionNum = $rows["SEC"];

				if(substr($sectionNum, 0, 1) == "L" || substr($sectionNum, 0, 1) == "P" || substr($sectionNum, 0, 1) == "D"){
					$sectionNum = substr($sectionNum, 1);
					if(substr($sectionNum, 1) == "A" || substr($sectionNum, 1) == "B" || substr($sectionNum, 1) == "C" || substr($sectionNum, 1) == "D"){
						$sectionNum = "0".substr($sectionNum, 0, -2);
						$multipleOptions = true;
					}
				}
				else if(intval($sectionNum) < 10){
					$sectionNum = "0".intval($sectionNum);
				}

				if(!isset($rows["INSTR FN"])){
					$rows["INSTR FN"] = "";
				}
				if(!isset($rows["LASTNAME"])){
					$rows["LASTNAME"] = "";
				}

				if(!isset($tempSection[$sectionNum]) && !$multipleOptions){
					$tempSection[$sectionNum] = $this->makeNewSection($section, $title, $rows);
				}
				else if(!$multipleOptions){
					$tempSec = $tempSection[$sectionNum];
					foreach($rows as $k => $v){
						if($k == $v){
							$tempSec->addTime($v, $rows["BEGIN"], $rows["END"]);
						}
					}
					if(!(array_search($rows["CRN"], $tempSec->getCRN()) > -1)){
						$tempSec->addCRN($rows["CRN"]);
					}
					$tempSec->setProf($rows["INSTR FN"]." ".$rows["LASTNAME"]);
					$tempSection[$sectionNum] = $tempSec;
				}
				else if($multipleOptions){
					array_push($manyOptions, $this->makeNewSection($section, $title, $rows));
				}
			}

			/** @var array|Section $tempSection **/
			foreach($tempSection as $k=>$v){
				if($multipleOptions){
					foreach($manyOptions as $optionalSection){
						if($optionalSection->getCourseNumber() == $v->getCourseNumber() &&
							$optionalSection->getFieldOfStudy() == $v->getFieldOfStudy() &&
							!$v->conflictsWithTime($optionalSection)){

							$newSec = new Section($v->getCourseTitle(), $v->getFieldOfStudy(), $v->getCourseNumber(), $v->getNumUnits(), $v->getCRN());
							if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
								$newSec->setRequiredCourse(true);
							}
							foreach($optionalSection->meetingTime as $day=>$times){
								foreach($times as $timeKey=>$time){
									$newSec->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
									$newSec->setMultiples(true);
								}
							}
							foreach($v->meetingTime as $day=>$times){
								foreach($times as $timeKey=>$time){
									$newSec->addTime($day, date("g:i a", $time["from"]), date("g:i a", $time["to"]));
								}
							}
							foreach($optionalSection->getCRN() as $crn){
								if(!(array_search($crn, $newSec->getCRN()) > -1)){
									$newSec->addCRN($crn);
								}
							}
							$newSec->setColor($courseColor);
							$this->allSections[] = $newSec;
						}
					}
				}
				else{
					$v->setColor($courseColor);
					$this->allSections[] = $v;
				}
			}
		}

		$this->generatePreregisteredSections();
		$this->removeSectionsForTime();
	}

}
