<?php
/**
 * PHP class for JavaScript minification  using UglifyJS by exec command.
 * https://github.com/orlleite/uglifyjs-php-exec
 *
 * Created by Makis Tracend (@tracend)
 * Adapted by Orlando Leite (@orlleite)
 * Released under the [Apache License v2.0](http://www.apache.org/licenses/LICENSE-2.0)
 */
class UglifyJS {

	var $_srcs = array();
	var $_options = "-m -c";
	var $_debug = true;
	var $_cache_dir = "";
	var $_timestamp = 0;
	
	function UglifyJS() { }

	/**
	 * Adds a source file to the list of files to compile. Files will be
	 * concatenated in the order they are added.
	 */
	function add($file) {
		$this->_srcs[] = $file;
		return $this;
	}

	/**
	 * Sets the directory where the compilation results should be cached
	 */
	function cacheDir($dir) {
		$this->_cache_dir = $dir;
		return $this;
	}

	function setFile( $name=false ) {
		if($name) $this->_file = $name;
		return $this;
	}

	/**
	 * Set compiler options
	 */
	function setOptions( $opt )
	{
		$this->_options = $opt;
	}
	
	/**
	 * Get compiler options
	 */
	function getOptions()
	{
		return $this->_options;
	}

	/**
	 * Writes the compiled response.  Reading from either the cache, or
	 * invoking a recompile, if necessary.
	 */
	function write( $output=false ) {

		// No cache directory so just dump the output.
		if ($this->_cache_dir == "") {
			echo $this->_compile();

		} else {
			$cache_file = $this->_getCacheFileName();
			if ($this->_isRecompileNeeded($cache_file)) {
				$result = $this->_compile();
				file_put_contents($cache_file, $result);
				if( $output ){
					echo $result;
				}
			} else {
				// No recompile needed, but see if we need to output the cached file.
				if( $output ){
					// Read the cache file and send it to the client.
					echo file_get_contents($cache_file);
				}
			}
		}
	}

	// removes source files (usually after compilation)
	function clear(){
		foreach ($this->_srcs as $i => $src) {
			unlink($src);
			unset($this->_srcs[$i]);
		}
	}

	// set a timestamp to compare the compiling against
	function timestamp( $time = null ){
		// prerequisite
		if( !is_int ( $time ) ) return;
		$this->_timestamp =  $time;
	}

	// ----- Privates -----

	function _isRecompileNeeded($cache_file) {
		// If there is no cache file, we obviously need to recompile.
		if (!file_exists($cache_file)) return true;

		$cache_mtime = filemtime($cache_file);

		// #1 If a specific time is set, use that as a reference
		if ( !empty( $this->_timestamp ) ) return ( $this->_timestamp > $cache_mtime );

		// #2 If the source files are newer than the cache file, recompile.
		foreach ($this->_srcs as $src) {
			if (filemtime($src) > $cache_mtime) return true;
		}

		// #3 If this script calling the compiler is newer than the cache file,
		// recompile.  Note, this might not be accurate if the file doing the
		// compilation is loaded via an include().
		if (filemtime($_SERVER["SCRIPT_FILENAME"]) > $cache_mtime) return true;

		// Cache is up to date.
		return false;
	}

	function _compile() {
		// No debug info?
		putenv('PATH='.getenv('PATH').':/usr/local/bin:/usr/bin:/bin:/usr/sbin:/sbin');
		exec( 'uglifyjs '.implode( ' ', $this->_srcs ).' '.$this->_options.' warnings=false 2>&1', $output, $rtnVal);
		return implode("\n", $output );
	}

	function _getCacheFileName() {
		return ( empty($this->_file) ) ? $this->_cache_dir . $this->_getHash() . ".js" : $this->_cache_dir . $this->_file. ".js";
	}

	function _getHash() {
		return md5(implode(",", $this->_srcs) . "-" . $this->_options);
	}

	function _readSources() {
		$code = "";
		foreach ($this->_srcs as $src) {
			$code .= file_get_contents($src) . "\n\n";
		}
		return $code;
	}

	function _unchunk($data) {
		$fp = 0;
		$outData = "";
		while ($fp < strlen($data)) {
			$rawnum = substr($data, $fp, strpos(substr($data, $fp), "\r\n") + 2);
			$num = hexdec(trim($rawnum));
			$fp += strlen($rawnum);
			$chunk = substr($data, $fp, $num);
			$outData .= $chunk;
			$fp += strlen($chunk);
		}
		return $outData;
	}

}
