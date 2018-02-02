<?php

trait Metadata {

	private $_metadata = array();

	public function addMeta( $key, $value ) {
		$this->_metadata[ $key ] = $value;
		return $this;
	}

	public function getMeta( $key ) {
		return $this->_metadata[ $key ];
	}

	public function getAllMeta() {
		return $this->_metadata;
	}

	public function copyMetaFrom( $object ) {
		if( !method_exists($object, 'getAllMeta') )
			return $this;
		foreach( $object->getAllMeta() as $key => $value ) {
			$this->addMeta( $key, $value );
		}
		return $this;
	}
}