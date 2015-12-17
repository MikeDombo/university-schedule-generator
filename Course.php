<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

This class is used only as a parent for the Section class and contains information that is the same for every 
section of a course.  This includes the course name, course number, field of study, and number of units
**/
class Course{
	private $courseName;
	private $courseNumber;
	private $fieldOfStudy;
	private $numUnits;
	
	/**
	Constructor accepting course title, field of study, course number, and number of units
	**/
	public function __construct($courseTitle, $fos, $courseNum, $units){
		$this->courseName = $courseTitle;
		$this->fieldOfStudy = $fos;
		$this->numUnits = $units;
		$this->courseNumber = $courseNum;
	}
	
	/**
	Accessors of variables
	**/
	public function getCourseTitle(){
		return $this->courseName;
	}
	
	public function getCourseNumber(){
		return $this->courseNumber;
	}
	
	public function getFieldOfStudy(){
		return $this->fieldOfStudy;
	}
	
	public function getNumUnits(){
		return $this->numUnits;
	}
}
?>
