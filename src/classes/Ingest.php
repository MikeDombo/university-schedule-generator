<?php

class Ingest {
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
	/** @var \MySQLDAL $dal */
	private $dal;
	/** @var int $classCount */
	private $classCount;
	/** @var array $daysWithUnwantedTimes */
	private $daysWithUnwantedTimes = [];
	/** @var string $timeFormatCode time format */
	private $timeFormatCode = "g:i a";


	/**
	 * Ingest constructor.
	 *
	 * @param \MySQLDAL $dal
	 * @param string $data
	 * @internal param \PDO $pdo
	 */
	public function __construct(MySQLDAL $dal, $data){
		$this->requestData = json_decode($data, true);

		$this->courseInput = $this->requestData["allCourses"];
		$this->preregistered = isset($this->requestData["preregistered"]) ? $this->requestData["preregistered"] : [];
		$this->morning = isset($this->requestData["timePref"]) ? $this->requestData["timePref"] : false;
		$this->allowFull = isset($this->requestData["fullClasses"]) ? $this->requestData["fullClasses"] : true;
		$this->startTime = isset($this->requestData["startTime"]) ?
			strtotime($this->requestData["startTime"]) : strtotime("8:00 AM");
		$this->endTime = isset($this->requestData["endTime"]) ?
			strtotime($this->requestData["endTime"]) : strtotime("10:00 PM");
		$this->unwantedTimes = isset($this->requestData["unwantedTimes"]) ? $this->requestData["unwantedTimes"] : [];

		$this->dal = $dal;
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
	 * Generates entries in the private variable $allSections
	 */
	public function generateSections(){
		foreach($this->courseInput as $section){
			if(!isset($section["FOS"]) || !isset($section["CourseNum"]) || !isset($section["Title"])){
				continue;
			}
			if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
				$this->requiredCourseNum++;
			}

			$result = $this->dal->fetchBySubjAndNumber($section["CourseNum"], $section["FOS"]);
			if($result == null){
				continue;
			}

			$tempSection = [];
			$manyOptions = [];
			$multipleOptions = false;
			$title = $section["Title"];
			foreach($result as $rows){
				if(!$this->allowFull && $rows[COLUMNS_ENROLLMENT_MAX] <= $rows[COLUMNS_ENROLLMENT_CURRENT]){
					continue;
				}
				if((($rows[COLUMNS_FOS] == "FYS" || $rows[COLUMNS_FOS] == "WELL" ||
							strpos($title, "ST:") > -1 || strpos($title, "SP:") > -1 ||
							($rows[COLUMNS_FOS] == "HIST" && $rows[COLUMNS_COURSE_NUM] == "199") ||
							($rows[COLUMNS_FOS] == "BIOL" && $rows[COLUMNS_COURSE_NUM] == "199") ||
							($rows[COLUMNS_FOS] == "ENGL" && $rows[COLUMNS_COURSE_NUM] == "299")) &&
						$rows[COLUMNS_COURSE_TITLE] != $title) &&
					(!(strpos($rows[COLUMNS_COURSE_TITLE], " LAB") > -1 ||
						strpos($title, " LAB") > -1))
				){
					continue;
				}
				if(isset($section["displayTitle"])){
					$title = $section["displayTitle"];
				}
				$sectionNum = $rows[COLUMNS_COURSE_SECTION];

				if(substr($sectionNum, 0, 1) == "L" || substr($sectionNum, 0, 1) == "P" || substr($sectionNum, 0, 1) == "D"){
					$sectionNum = substr($sectionNum, 1);
					if(substr($sectionNum, 1) == "A" || substr($sectionNum, 1) == "B" || substr($sectionNum, 1) == "C" || substr($sectionNum, 1) == "D"){
						$sectionNum = "0" . substr($sectionNum, 0, -2);
						$multipleOptions = true;
					}
				}
				else if(intval($sectionNum) < 10){
					$sectionNum = "0" . intval($sectionNum);
				}

				if(!isset($rows[COLUMNS_PROF_FN])){
					$rows[COLUMNS_PROF_FN] = "";
				}
				if(!isset($rows[COLUMNS_PROF_LN])){
					$rows[COLUMNS_PROF_LN] = "";
				}

				if(!isset($tempSection[$sectionNum]) && !$multipleOptions){
					$tempSection[$sectionNum] = $this->makeNewSection($section, $title, $rows);
				}
				else if(!$multipleOptions){
					$tempSec = $tempSection[$sectionNum];
					foreach($rows as $k => $v){
						if($k == $v){
							$tempSec->addTime($v, $rows[COLUMNS_TIME_BEGIN], $rows[COLUMNS_TIME_END]);
						}
					}
					if(!(array_search($rows[COLUMNS_CRN], $tempSec->getCRN()) > -1)){
						$tempSec->addCRN($rows[COLUMNS_CRN]);
					}
					$tempSec->setProf($rows[COLUMNS_PROF_FN] . " " . $rows[COLUMNS_PROF_LN]);
					$tempSection[$sectionNum] = $tempSec;
				}
				else if($multipleOptions){
					$manyOptions[] = $this->makeNewSection($section, $title, $rows);
				}
			}

			$courseColor = $this->generateColor([255, 255, 255]);
			/** @var array|Section $tempSection * */
			foreach($tempSection as $k => $v){
				if($multipleOptions){
					foreach($manyOptions as $optionalSection){
						if($optionalSection->getCourseNumber() == $v->getCourseNumber() &&
							$optionalSection->getFieldOfStudy() == $v->getFieldOfStudy() &&
							!$v->conflictsWithTime($optionalSection)
						){

							$newSec = new Section($v->getCourseTitle(), $v->getFieldOfStudy(),
							$v->getCourseNumber(), $v->getNumUnits(), $v->getCRN());
							if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
								$newSec->setRequiredCourse(true);
							}
							foreach($optionalSection->meetingTime as $day => $times){
								foreach($times as $timeKey => $time){
									$newSec->addTime($day, date($this->timeFormatCode, $time["from"]), date($this->timeFormatCode, $time["to"]));
									$newSec->setMultiples(true);
								}
							}
							foreach($v->meetingTime as $day => $times){
								foreach($times as $timeKey => $time){
									$newSec->addTime($day, date($this->timeFormatCode, $time["from"]), date($this->timeFormatCode, $time["to"]));
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

		$preregSections = $this->generatePreregisteredSections($this->preregistered);
		foreach($preregSections as $v){
			$this->allSections[] = $v;
		}

		// Set the count of classes to know if a schedule has all the requested classes
		$this->classCount = count($this->courseInput) + count($preregSections);
		$this->daysWithUnwantedTimes = $this->makeDaysWithUnwantedTimes($this->unwantedTimes);
		$this->allSections = $this->removeSectionsForTime($this->allSections, $this->startTime, $this->endTime,
			$this->daysWithUnwantedTimes, $this->unwantedTimes);
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
		if(!isset($rows[COLUMNS_PROF_FN])){
			$rows[COLUMNS_PROF_FN] = "";
		}
		if(!isset($rows[COLUMNS_PROF_LN])){
			$rows[COLUMNS_PROF_LN] = "";
		}

		$tempSec = new Section($title, $rows[COLUMNS_FOS], $rows[COLUMNS_COURSE_NUM], floatval
		($rows[COLUMNS_UNITS]), [$rows[COLUMNS_CRN]]);

		if(isset($section["requiredCourse"]) && $section["requiredCourse"]){
			$tempSec->setRequiredCourse(true);
		}
		foreach($rows as $k => $v){
			if($k == $v){
				$tempSec->addTime($v, $rows[COLUMNS_TIME_BEGIN], $rows[COLUMNS_TIME_END]);
			}
		}
		$tempSec->setProf($rows[COLUMNS_PROF_FN] . " " . $rows[COLUMNS_PROF_LN]);

		return $tempSec;
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
	 * Generates the section variables for all preregistered classes and adds them to $allSections
	 * Also sets the class count variable
	 *
	 * @param array|string $preregistered
	 * @return array|Section
	 */
	private function generatePreregisteredSections($preregistered){
		$preregSections = [];
		foreach($preregistered as $crn){
			$result = $this->dal->fetchByCRN($crn);
			if($result == null){
				continue;
			}
			foreach($result as $rows){
				$tempSec = $this->makeNewSection([], $rows[COLUMNS_COURSE_TITLE], $rows);
				$tempSec->setColor($this->generateColor([255, 255, 255]));
				if(count($tempSec->meetingTime) > 0){
					$preregSections[] = $tempSec;
				}

			}
		}

		// Try to merge preregistered sections if they are the same course (ex. class and a lab section)
		for($i = 0; $i < count($preregSections); $i += 1){
			for($j = 0; $j < count($preregSections); $j += 1){
				if($i == $j){
					continue;
				}
				$v = $preregSections[$i];
				$v2 = $preregSections[$j];
				if((similar_text($v->getCourseTitle(), $v2->getCourseTitle()) >= 10 || $v->getCourseTitle() == $v2->getCourseTitle())
					&& $v->getCourseNumber() == $v2->getCourseNumber() && $v->getFieldOfStudy() == $v2->getFieldOfStudy()){
					foreach($v2->getCRN() as $crn){
						if(!(array_search($crn, $v->getCRN()) > -1)){
							$v->addCRN($crn);
						}
					}
					foreach($v2->meetingTime as $day => $times){
						foreach($times as $time){
							$v->addTime($day, date($this->timeFormatCode, $time["from"]), date($this->timeFormatCode, $time["to"]));
						}
					}
					unset($preregSections[$j]);
					$preregSections = array_values($preregSections);
				}
			}
		}

		foreach($preregSections as $v){
			$v->preregistered = true;
		}

		return $preregSections;
	}

	/**
	 * Generates the daysWithUnwatedTimes array which is an array of all the days which may have blacked out times
	 *
	 * @param $unwantedTimes
	 * @return array
	 */
	private function makeDaysWithUnwantedTimes($unwantedTimes){
		$daysWithUnwantedTimes = [];
		foreach($unwantedTimes as $v){
			foreach(array_keys($v) as $k2){
				$intDay = Schedule::intToDay(Schedule::dayToInt($k2));
				if(!in_array($intDay, $daysWithUnwantedTimes)){
					$daysWithUnwantedTimes[] = $intDay;
				}
			}
		}

		return $daysWithUnwantedTimes;
	}

	/**
	 * Removes sections from the $allSections list if there is a problem with the time it meets
	 * ie. it is on a day, during a time that is blacked out, or it occurs outside of the start and end times chosen
	 *
	 * @param $allSections
	 * @param $startTime
	 * @param $endTime
	 * @param $daysWithUnwantedTimes
	 * @param $unwantedTimes
	 * @return mixed
	 */
	private function removeSectionsForTime($allSections, $startTime, $endTime, $daysWithUnwantedTimes, $unwantedTimes){
		/** @var Section $section */
		foreach($allSections as $key => $section){
			if($section->preregistered){
				continue;
			}
			if($startTime > $section->getEarliestTime()[1] || $endTime < $section->getLatestTime()[1]){
				unset($allSections[$key]);
				continue;
			}
			foreach($section->meetingTime as $day => $times){
				if(!in_array($day, $daysWithUnwantedTimes)){
					continue;
				}
				foreach($times as $days => $time){
					foreach($unwantedTimes as $dayVal){
						foreach($dayVal as $val){
							if(Schedule::intToDay(Schedule::dayToInt($val)) != $days){
								continue;
							}
							else if(strtotime($dayVal["startTime"]) < $time["to"] && strtotime($dayVal["endTime"]) > $time["from"]){
								unset($allSections[$key]);
								continue 5;
							}
						}
					}
				}
			}
		}

		return $allSections;
	}

}
