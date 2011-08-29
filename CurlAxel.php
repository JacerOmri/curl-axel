<?php
/*
	CurlAxel Class by JokerHacker
	this class is used to make parallel multiconnection to download files.
	this is the first release, for feedback send mail to jokerhacker.jacer@gmail.com
	This class is made to help PHP community. Pleas keep it away from commercial use.

*/

class CurlAxel {
	private $url = "";
	private $optarray = array();
	private $megaconnection;
	private $partnames = array();
	private $tempdir = "/temp/";
	private $downdir = "/downloads/";
	private $partcount = 5;
	private $log = false;
	private $buffersize = 67108864;
	public $version = "0.1 29/08/11";
	
	function __construct() {
		$this->megaconnection = curl_multi_init();
		$this->tempdir = getcwd() . $this->tempdir;
		if(!is_dir($this->tempdir)) mkdir($this->tempdir);
		$this->downdir = getcwd() . $this->downdir;
		if(!is_dir($this->downdir)) mkdir($this->downdir);
	}
	
	public function activeLog($is) {
		$this->log = (bool)$is;
	}
	
	public function setParts($num) {
		$this->partcount = (int)$num;
	}
	
	public function setTempDir($dir) {
		$this->tempdir = $dir;
		if(!is_dir($this->tempdir)) mkdir($this->tempdir);
	}
	
	public function setDownloadDir($dir) {
		$this->downdir = $dir;
		if(!is_dir($this->downdir)) mkdir($this->downdir);
	}
		
	public function setCurlOpts($opts) {
		$this->optarray = $opts;
	}
	
	public function getFileSize($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		curl_close($ch);
		if (preg_match('/Content-Length: (\d+)/', $data, $matches))
			return $contentLength = (int)$matches[1];
		else return false;
}

	public function setUrl($url) {
		$pattern = "/(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?/i";
		if (preg_match($pattern, $url)) {
			$this->url = strtolower($url);
			return true;
		}
		else return false;
	}

	public function setBufferSize($buffersie){
		$this->buffersize = $buffersie;
	}
	
	private function parseFile() {
		$filename = basename($this->url);
		$size = $this->getFileSize($this->url);
		$splits = range(0, $size, ceil($size/$this->partcount));
		$this->filename = $filename;
		$this->size = $size;
		$this->splits = $splits;
	}

	public function download() {
		$this->parseFile();
		if($this->log) $log = fopen($this->tempdir . 'log.txt', 'a+');
		for ($i = 0; $i < sizeof($this->splits); $i++) {
			$ch[$i] = curl_init();
			//default user agent, can be changed by user option array
			curl_setopt($ch[$i], CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/14.0.835.29 Safari/535.1");
			//user option array
			curl_setopt_array($ch[$i], $this->optarray);
			//CurlAxel config
			curl_setopt($ch[$i], CURLOPT_URL, $this->url);
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, 0); 
			curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, 1); 
			if($this->log) {
				curl_setopt($ch[$i], CURLOPT_VERBOSE, 1);
				curl_setopt($ch[$i], CURLOPT_STDERR, $log); 
			}
			curl_setopt($ch[$i], CURLOPT_BINARYTRANSFER, 1);
			curl_setopt($ch[$i], CURLOPT_FRESH_CONNECT, 0);
			curl_setopt($ch[$i], CURLOPT_CONNECTTIMEOUT, 10);
			$this->partnames[$i] = $this->filename . $i;
			$bh[$i] = fopen($this->tempdir . $this->partnames[$i], 'w+');
			curl_setopt($ch[$i], CURLOPT_FILE, $bh[$i]);
				$x = ($i == 0 ? 0 : $this->splits[$i]+1);
				$y = ($i == sizeof($this->splits)-1 ? $this->size : $this->splits[$i+1]);
				$range = $x.'-'.$y;
			curl_setopt($ch[$i], CURLOPT_RANGE, $range);
			curl_multi_add_handle($this->megaconnection, $ch[$i]);
		}
		$active = null;
		do {
			$mrc = curl_multi_exec($this->megaconnection, $active);
		} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		while ($active && $mrc == CURLM_OK) {
			if (curl_multi_select($this->megaconnection) != -1) {
				do {
					$mrc = curl_multi_exec($this->megaconnection, $active);
				} while ($mrc == CURLM_CALL_MULTI_PERFORM);
			}
		}
		$finalpath = $this->downdir . $this->filename;
		$final = fopen($finalpath, "w+");
		for ($i = 0; $i < sizeof($this->splits); $i++) {
			$partpath = $this->tempdir . $this->partnames[$i];
			fseek($bh[$i], 0, SEEK_SET);
			while (!feof($bh[$i])) {
				$contents = fread($bh[$i], $this->buffersize);
				fwrite($final, $contents);
			}
			fclose($bh[$i]);
			unlink($partpath);
		}
		fclose($final);
	}
}
