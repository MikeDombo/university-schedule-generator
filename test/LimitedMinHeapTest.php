<?php
/**
 * Created by PhpStorm.
 * User: Mike
 * Date: 6/10/2017
 * Time: 9:53 AM
 */

use \PHPUnit\Framework\TestCase;

class LimitedMinHeapTest extends TestCase{
	public function test(){
		$heap = new LimitedMinHeap(2);
		$heap->insert(5);
		$this->assertEquals(1, $heap->count());
		$this->assertEquals(5, $heap->peek());
		$this->assertEquals(5, $heap->pop());
		$heap->insert(5);
		$this->assertEquals([5], $heap->getMinArray());
		$this->assertEquals(0, $heap->count());
		$this->assertEquals([], $heap->getMinArray());
		$this->assertEquals([], $heap->getMaxArray());

		$heap->insert(5);
		$heap->insert(4);
		$this->assertEquals(4, $heap->peek());
		$this->assertEquals([4, 5], $heap->getMinArray());

		$heap->insert(5);
		$heap->insert(4);
		$heap->insert(3);
		$heap->insert(100);
		$this->assertEquals(5, $heap->peek());
		$this->assertEquals([5, 100], $heap->getMinArray());

		$heap->insert(5);
		$heap->insert(4);
		$heap->insert(3);
		$heap->insert(100);
		$this->assertEquals([100, 5], $heap->getMaxArray());

		$heap->insert(5);
		$heap->insert(4);
		$heap->insert(4);
		$heap->insert(100);
		$this->assertEquals([100, 5], $heap->getMaxArray());
	}
}
