<?php

class NumberList extends CacheableResultObject {

	protected $_list        = array();

	public function addNumber( $number ) {
		if( !is_numeric($number) )
			Throw new Exception("NumberList can only accept numeric values");
		$this->_list[]      = $number;
		$this->_resultCache = array();
	}

	protected function _cacheable_min() {
		return min( $this->_list );
	}

	protected function _cacheable_max() {
		return max( $this->_list );
	}

	protected function _cacheable_average() {
		return $this->sum() / count($this->_list);
	}

	protected function _cacheable_sum() {
		return array_sum( $this->_list );
	}

	protected function __cacheable_median() {
		return $this->quartile( 0.5 );
	}

	protected function _cacheable_quartile25() {
		return $this->quartile( 0.25 );
	}

	protected function _cacheable_quartile75() {
		return $this->quartile( 0.75 );
	}

	protected function _cacheable_quartile( $position ) {

		$this->sortList();
		
		$quartPosExact = (count($this->_list) - 1) * $position;
		$quartPosIndex = floor($quartPosExact);
		$remainder     = $quartPosExact - $quartPosIndex;

		$baseValue = $this->_list[ $quartPosIndex ];
		if( ! isset($this->_list[$quartPosIndex+1]) ) {
			return $baseValue;
		}

		$interpolatedDiff = $remainder * ($this->_list[$quartPosIndex+1] - $this->_list[$quartPosIndex]);
		return $baseValue + $interpolatedDiff;
	}


	protected function sortList() {
		sort( $this->_list );
	}

}