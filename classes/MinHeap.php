<?php
/**
Authored by Michael Dombrowski, http://mikedombrowski.com
Original repository available at http://github.com/md100play/university-schedule-generator

This file contains 2 classes, LimitedMinHeap which drives MinHeap
LimitedMinHeap is a min heap that has a maximum size in order to find the $maxSize number of highest values in the heap
**/

class MinHeap extends SplMinHeap
{
	/**
	Custom comparison method to compare schedule scores
	**/
    public function compare($item1, $item2) {
        if($item1->score == $item2->score){
			return 0;
		}
		return $item1->score > $item2->score ? -1 : 1; 
    }
}
