<?php
class MinHeap extends SplMinHeap
{
    public function compare($item1, $item2) {
        if($item1->score == $item2->score){
			return 0;
		}
		return $item1->score > $item2->score ? -1 : 1; 
    }
}

class LimitedMinHeap {
	private $minHeap;
	private $maxSize;
	
	public function __construct($maxSize=100){
		$this->minHeap = new MinHeap();
		$this->maxSize = $maxSize;
	}
	
	public function insert($a){
		if($this->minHeap->count() >= $this->maxSize){
			if($this->minHeap->top()->score < $a->score){
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
}
?>