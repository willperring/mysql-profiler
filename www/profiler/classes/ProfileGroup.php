<?php

class ProfileGroup {

	private $_profiles = array();
	private $_queryTimeMetrics;

	public function addProfileLog( ProfileLog $profile ): ProfileGroup {

		$hue   = ( count($this->_profiles) * 0.37 ) * 255; // one third, plus a third of a third.
		while( $hue > 255 ) { $hue -= 255; }

		$color = HexColor::createFromHSL( $hue, 255, 100 );
		$profile->addMeta('color',   $color);
		$profile->addMeta('request', count($this->_profiles)+1);
		$this->_profiles[] = $profile;

		return $this;
	}

	public function getChartData() {
		$queries = $this->getAllQueries();
		$chart   = array(
			array('Table', 'Time')
		);

		foreach( $queries as $query ) {
			$chart[] = array(
				$query->getTableString() . ' : ' . $query->getSql(),
				$query->getTime() * 1000,
			);
		}

		return $chart;
	}

	public function getGroupedQueriesList( $field='time' ): MetricList {
		$queries = $this->getAllQueries();
		$results = new MetricList();

		$this->_queryTimeMetrics = new NumberList();

		foreach( $queries as $query ) {

			$ql = $results->getChild( $query->getTableString() )
				->addMeta('displayTable', $query->getFullTable())
				->offsetValue( $query->getTime() )
				->getChild( $query->getType() )
				->offsetValue( $query->getTime() );
			if( $field=='sql')
				$ql->setChildSortFunction('name','string');

			// Add the last one manually as we want to see repeats
			$metric = new SortableMetric($query->getSql(), $query->getTime());
			$metric->copyMetaFrom( $query );
			$ql->addChild( $metric );

			$this->_queryTimeMetrics->addNumber( $metric->value );
		}

		return $results->sort();
	}

	public function getProfiles() {
		return $this->_profiles;
	}

	public function getTimeMetrics() {
		return $this->_queryTimeMetrics;
	}

	private function getAllQueries() {
		$queries = array();
		foreach( $this->_profiles as $profile ) {
			$queries = array_merge( $queries, $profile->getQueries() );
		}
		return $queries;
	}

}