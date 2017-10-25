<?php

class ScheduleGenerateBronKerbosch extends \ScheduleGenerate {
	private $allSections;
	private $neighborSections;

	public function generateSchedules($allSections){
		$this->sectionCount = count($allSections);
		$sectionIDs = [];
		foreach($allSections as $s){
			/** @var $s \Section */
			$sectionIDs[] = $s->getId();
			$this->allSections[$s->getId()] = $s;
		}

		$this->run([], $sectionIDs, []);
	}

	private function neighborMemoizer($sId){
		if(!isset($this->neighborSections[$sId])){
			$neighbors = array_map(
				function(Section $i){
					return $i->getId();
				},
				array_filter($this->allSections,
					function(Section $s) use ($sId){
						return $this->sectionsAreCompatible($this->allSections[$sId], $s);
					}
				)
			);
			$this->neighborSections[$sId] = $neighbors;
		}

		return $this->neighborSections[$sId];
	}

	private function run(array $r, array $p, array $x){
		if(empty($p) && empty($x)){
			$requiredCourses = 0;
			$a = new Schedule();
			foreach($r as $b){
				$b = $this->allSections[$b];
				/** @var $b \Section */
				if($b->isRequiredCourse()){
					$requiredCourses++;
				}
				$a->addSection($b);
			}

			if($requiredCourses == $this->ingest->getRequiredCourseNum()){
				$a->setScore($this->ingest->getMorning());
				$this->schedules->insert($a);
				$this->numSchedules++;
			}
		}

		foreach($p as $v){
			$neighbors = $this->neighborMemoizer($v);
			$this->run(array_merge($r, [$v]), array_intersect($p, $neighbors), array_intersect($x, $neighbors));

			$p = array_diff($p, [$v]);
			$x = array_values(array_merge($x, [$v]));
		}
	}

}
