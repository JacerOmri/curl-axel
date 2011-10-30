<?php
/*
**************************************************************************************
*	CurlAxel Class by JokerHacker
*	this class is used to make parallel multiconnection to download files.
*	this is the first release, for feedback send mail to jokerhacker.jacer@gmail.com
*	This class is made to help PHP community. Pleas keep it away from commercial use.
**************************************************************************************
*/

class CurlAxel {
	private $url = "";
	private $optarray = array();
	private $megaconnection;
	private $partnames = array();
	private $tempdir = "/temp/";
	private $downdir = "/downloads/";
	private $partcount = 5;
	private $progress = false;
	private $cookies = false;
	private $log = false;
	
	// as default, buffer size is set to 64Mb
	private $buffersize = 67108864; 
	
	public $version = "0.5 30/10/11";
	
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
		if(!is_dir($this->tempdir)) {
			if(mkdir($this->tempdir)) {
				return true;
			}
		}
		return false;
	}
	
	public function setDownloadDir($dir) {
		$this->downdir = $dir;
		if(!is_dir($this->downdir)) {
			if(mkdir($this->downdir)) {
				return true;
			}
		}
		return false;
	}
	
	public function setCookies($cookies) {
		$this->cookies = (string)$cookies;
	}
	
	public function setCurlOpts($opts) {
		$this->optarray = $opts;
	}
		
	public function setProgressCallback() {
		$this->progress = true;
	}
		
	public function getFileSize($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		$data = curl_exec($ch);
		$filesize = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		curl_close($ch);
		if ($filesize)
			return $filesize;
		else return false;
	}
	public function isMT($url) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_RANGE, 100-200);
		curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		if($info['download_content_length'] != 100) return false;
		else return true;
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
		$this->buffersize = (int)$buffersie;
	}
	
	private function parseFile() {
		$filename = basename($this->url);
		$size = $this->getFileSize($this->url);
		$splits = range(0, $size, ceil($size/$this->partcount));
		$this->filename = $filename;
		$this->size = $size;
		$this->splits = $splits;
	}

	public function fast_download() {
		$this->parseFile();
		if($this->log) $log = fopen($this->tempdir . 'log.txt', 'a+');
		for ($i = 0; $i < sizeof($this->splits)-1; $i++) {
			$ch[$i] = curl_init();
			
			//user option array
			curl_setopt_array($ch[$i], $this->optarray);
			
			//CurlAxel config
			curl_setopt($ch[$i], CURLOPT_URL, $this->url);
			curl_setopt($ch[$i], CURLOPT_RETURNTRANSFER, false); 
			curl_setopt($ch[$i], CURLOPT_FOLLOWLOCATION, true); 
			if($this->log) {
				curl_setopt($ch[$i], CURLOPT_VERBOSE, true);
				curl_setopt($ch[$i], CURLOPT_STDERR, $log); 
			}
			
			//set the progress CallBack function
			if ($this->progress) {
				curl_setopt($ch[$i], CURLOPT_NOPROGRESS, false);
				echo '<span id="pb'. $i .'"></span>
				<script>(function() {$("#pb'. $i .'").progressBar();})</script>';
				$progress = create_function('$download_size, $downloaded, $upload_size, $uploaded','static $sprog = 0;
				@$prog = ceil($downloaded*100/$download_size);
				if(!isset($time)) static $time = 0;
				if (($prog > $sprog) and ((time() >= $time+1) or ($time == 0) or ($downloaded ==  $download_size))){
				   $sprog = $prog;
				   echo \'<script>$("#pb'. $i .'").progressBar(\'. $sprog. \');</script>\';
				   $time = time();
				}');
				curl_setopt($ch[$i], CURLOPT_PROGRESSFUNCTION, $progress);
				curl_setopt($ch[$i], CURLOPT_BUFFERSIZE, 10*1024*1024);
			}
			
			//set the cookies
			if ($this->cookies) {
				curl_setopt($ch[$i], CURLOPT_COOKIE, $this->cookies);
			}
			
			curl_setopt($ch[$i], CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch[$i], CURLOPT_FRESH_CONNECT, true);
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
		for ($i = 0; $i < sizeof($this->splits)-1; $i++) {
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
	
	public function slow_download() {
		if($this->log) $log = fopen($this->tempdir . 'log.txt', 'a+');
		$filename = basename($this->url);
		$size = $this->getFileSize($this->url);
		$this->filename = $filename;
		$this->size = $size;
		$ch = curl_init();
		
		//user option array
		curl_setopt_array($ch, $this->optarray);
		
		//CurlAxel config
		curl_setopt($ch, CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, false); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
		if($this->log) {
			curl_setopt($ch, CURLOPT_VERBOSE, true);
			curl_setopt($ch, CURLOPT_STDERR, $log); 
		}
		
		//set the progress CallBack function
		if ($this->progress) {
			curl_setopt($ch, CURLOPT_NOPROGRESS, false);
			echo '<span id="pb1"></span>
			<script>(function() {$("#pb1").progressBar();})</script>';
			$progress = create_function('$download_size, $downloaded, $upload_size, $uploaded','static $sprog = 0;
			@$prog = ceil($downloaded*100/$download_size);
			if(!isset($time)) $time = 0;
			if (($prog > $sprog) and ((time() >= $time+1) or ($time == 0) or ($downloaded ==  $download_size))){
			if ($prog > $sprog){
				$sprog = $prog;
				echo \'<script>$("#pb1").progressBar(\'. $sprog. \');</script>\';
				$time = time();
			}');
			curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, $progress);
			curl_setopt($ch, CURLOPT_BUFFERSIZE, 10*1024*1024);
		}
		
		//set the cookies
		if ($this->cookies) {
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
		}
		
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		$bh = fopen($this->downdir . $this->filename , 'w+');
		curl_setopt($ch, CURLOPT_FILE, $bh);
		curl_exec($ch);
		curl_close($ch);
	}
	
	public function download() {
		$isMT = $this->isMT($this->url);
		$size = $this->getFileSize($this->url);
		if($isMT and $size > 5*1024*1024) {
			$this->fast_download();
		} else {
			$this->slow_download();
		}
	}
}
