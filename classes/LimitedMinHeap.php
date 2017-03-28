<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

LimitedMinHeap is a min heap that has a maximum size in order to find the $maxSize number of highest values in the heap
 **/

class LimitedMinHeap {
	private $minHeap;
	private $maxSize;

	public function __construct($maxSize=100){
		$this->minHeap = new MinHeap();
		$this->maxSize = $maxSize;
	}

	public function insert($a){
		if($this->minHeap->count() >= $this->maxSize){ //if heap is full
			if($this->minHeap->top()->score < $a->score){ //if the minimum of the heap is less than $a's score, then remove the minimum and add $a to the heap
				$this->minHeap->extract();
			}
			else{
				return;
			}
		}
		$this->minHeap->insert($a);
	}

	public function count(){
		return $this->minHeap->count();
	}

	public function peek(){
		return $this->minHeap->top();
	}

	public function pop(){
		$a = $this->minHeap->top();
		$this->minHeap->extract();
		return $a;
	}

	public function getArray(){
		$temp = [];
		$schedCount = $this->count();
		for($i=0; $i<$schedCount; $i++){
			$temp[] = $this->pop();
		}
		return $temp;
	}
}
