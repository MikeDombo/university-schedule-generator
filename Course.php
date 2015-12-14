<?php
class Course{
	private $courseName;
	private $courseNumber;
	private $fieldOfStudy;
	private $numUnits;
	
	public function __construct($courseTitle, $fos, $courseNum, $units){
		$this->courseName = $courseTitle;
		$this->fieldOfStudy = $fos;
		$this->numUnits = $units;
		$this->courseNumber = $courseNum;
	}
	
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
