<?php

class SortableMetric {

	use Metadata;

	private $_name;
	private $_value;
	private $_children;

	private $_first = false;

	public function __construct( $name, $value ) {
		$this->_name     = $name;
		$this->_value    = $value;
		$this->_children = new MetricList();
	}

	public function __get( $key ) {
		$propName = '_'.$key;
		if( property_exists($this, $propName) ) {
			return $this->$propName;
		}
		trigger_error("No property for key {$key}", E_USER_WARNING);
		return null;
	}

	public function addChild( $child ) {
		return $this->_children->addChild( $child );
	}

	public function getChild( $key, $strict=false) {
		return $this->_children->getChild( $key, $strict );
	}

	public function getChildCount() {
		return $this->_children->getChildCount();
	}

	public function getDescendantCount() {
		// Count a one if this has no children - to represent the end of a branch.
		return $this->_children->getDescendantCount() ?: 1;
	}

	public function offsetValue( $amount ) {
		$this->_value += $amount;
		return $this;
	}

	public function getChildList() {
		return $this->_children->sort();
	}

	public function setChildSortFunction( $field, $method, $dir=1 ) {
		$this->_children->setSortMetric($field, $method, $dir);
		return $this;
	}

	public function setFirstFlag( $state=true ) {
		$this->_first = $state;
		return $this;
	}

	public function isFirst() {
		return $this->_first == true;
	}

}