<?php

class MetricList implements Iterator {

	private $_list    = array();
	private $_pointer = 0;
	private $_sortFunction;

	public function __construct() {
		$this->_sortFunction = function($a,$b) {
			$diff = $b->value - $a->value; // b-a, we want descending order.
			return ($diff > 0) ? 1 : ($diff < 0 ? -1 : 0);
		};
	}

	public function current() {
		return $this->_list[ $this->_pointer ]
			->setFirstFlag( $this->_pointer == 0 );
	}

	public function rewind() {
		$this->_pointer = 0;
	}

	public function key() {
		return $this->_pointer;
	}

	public function next() {
		++$this->_pointer;
	}

	public function valid() {
		return isset($this->_list[ $this->_pointer ]);
	}

	public function addChild( SortableMetric $metric ) {
		$this->_list[] = $metric;
		return $this;
	}

	public function getChild( $key, $strict=false ): SortableMetric {

		foreach( $this->_list as $item ) {
			if( $key == $item->name )
				return $item;
		}

		if( $strict)
			return null;

		$newMetric = new SortableMetric( $key, 0 );
		$this->_list[] = $newMetric;
		return $newMetric;
	}

	public function getChildCount(): int {
		return count( $this->_list );
	}

	public function getDescendantCount(): int {
		$count = 0;
		foreach( $this->getChildren() as $child ) {
			$count += $child->getDescendantCount();
		}
		return $count;
	}

	public function getChildren() {
		return $this->_list;
	}

	public function getTotalChildValues() {
		$total = 0;
		foreach( $this->_list as $child ) {
			$total += $child->value;
		}
		return $total;
	}

	public function setSortMetric( $field, $method, $dir=1 ) {
		$this->_sortFunction = function($a,$b) use($field,$method, $dir) {
			switch( $method ) {
				case 'numeric':
					$diff = $b->$field - $a->$field;
					break;
				case 'string':
					$diff = strcmp( $a->$field, $b->$field );
					break;
				default:
					trigger_error("Unknown MetricList sort property, '{$field}'", E_USER_WARNING);
					$diff = 0;
					break;
			}
			return ($diff > 0) ? 1 : ($diff < 0 ? -1 : 0);
		};
		return $this;
	}

	public function sort() {
		usort( $this->_list, $this->_sortFunction );
		return $this;
	}


}