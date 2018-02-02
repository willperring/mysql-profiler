<?php

abstract class CacheableResultObject {

	protected $_resultCache = array();

	public function __call( $name, $arguments ) {

		$funcKey  = "_cacheable_" . $name;
		if( ! method_exists($this, $funcKey) )
			Throw new Exception(get_called_class() . " has no method {$name}");

		$cacheKey = $name . crc32( json_encode($arguments) );

		if( $this->checkCache( $cacheKey, $result ) ) {
			return $result;
		}

		$result = call_user_func_array(
			array( $this, $funcKey), $arguments
		);

		$this->_resultCache[ $cacheKey ] = $result;
		return $result;
	}

	protected function checkCache( $key, &$result ): bool {
		if( key_exists($key, $this->_resultCache) ) {
			$result = $this->_resultCache[ $key ];
			return true;
		}

		return false;
	}

}