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
			//if the minimum of the heap is less than $a's score, then remove the minimum and add $a to the heap
			if($this->minHeap->compare($this->minHeap->top(), $a) > 0){
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
		return $this->minHeap->extract();
	}

	public function getMinArray(){
		$temp = [];
		$schedCount = $this->count();
		for($i=0; $i<$schedCount; $i++){
			$temp[] = $this->pop();
		}
		return $temp;
	}

	public function getMaxArray(){
		$temp = [];
		$schedCount = $this->count();
		for($i=0; $i<$schedCount; $i++){
			array_unshift($temp, $this->pop());
		}
		return $temp;
	}
}
