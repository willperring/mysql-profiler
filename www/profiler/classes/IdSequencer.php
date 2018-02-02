<?php

class IdSequencer {

	private $_id = null;

	public function get() {
		$this->_id = uniqid();
		return $this->hash( $this->_id );
	}

	public function repeat() {
		return $this->hash( $this->_id );
	}

	private function hash( $id ) {
		return $id;
	}

}