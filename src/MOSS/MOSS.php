<?php

/// @cond

namespace CL\MOSS;

/*
	The MIT License (MIT)
	Copyright (c) 2014 Philipp Helo Rehs
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.

	Modified for my use in CourseLib by CBO:
		Added add_raw and raw file support.
		Adding capabilty to send logging information to a closure
*/

/**
 * @author Philipp Helo Rehs <P.Rehs@gmx.net>
 * @version 1.0
 * @moss-version 2.0
 */
class MOSS {
	private $allowed_languages = array("c", "cc", "java", "ml", "pascal", "ada", "lisp", "scheme", "haskell", "fortran", "ascii", "vhdl", "perl", "matlab", "python", "mips", "prolog", "spice", "vb", "csharp", "modula2", "a8086", "javascript", "plsql", "verilog");
	private $options = array();
	private $basefiles = array();
	private $files = array();
	private $server;
	private $port;
	private $userid;

	private $raw = array();

	public function add_raw($path, $text, $lang = "javascript") {
		if(strlen($text) < 100) {
			return;
		}

		$this->raw[] = array('path' => $path, 'text' => $text, 'lang' => $lang);
	}

	/**
	 * @param int  	  $userid
	 * @param string  $server
	 * @param integer $port
	 */
	public function __construct($userid, $server = "moss.stanford.edu",$port = 7690){
		$this->options['m'] = 10;
		$this->options['d'] = 0;
		$this->options['n'] = 250;
		$this->options['x'] = 0;
		$this->options['c'] = "";
		$this->options['l'] = "c";
		$this->server = $server;
		$this->port = $port;
		$this->userid = $userid;
	}
	/**
	 * set the language of the source files
	 * @param string $lang
	 */
	public function setLanguage($lang){
		if(in_array($lang, $this->allowed_languages)){
			$this->options['l'] = $lang;
			return true;
		}
		else{
			throw new \Exception("Unsupported language", 1);
		}
	}
	/**
	 * get a list with all supported languages
	 * @return array
	 */
	public function getAllowedLanguages(){
		return $this->allowed_languages;
	}
	/**
	 * Enable Directory-Mode
	 * @see -d in MOSS-Documentation
	 * @param bool $enabled
	 */
	public function setDirectoryMode($enabled){
		if(is_bool($enabled)){
			$this->options['d'] = (int)$enabled;
			return true;
		}
		else{
			throw new \Exception("DirectoryMode must be a boolean", 2);
		}
	}

	/**
	 * Add a basefile
	 * @see -b in MOSS-Documentation
	 * @param string $file
	 * @return bool true
	 * @throws \Exception On invalid paramters
	 */
	public function addBaseFile($file){
		if(file_exists($file) && is_readable($file)){
			$this->basefiles[] = $file;
			return true;
		}
		else{
			throw new \Exception("Can't find or read the basefile (".$file.")", 3);
		}
	}

	/**
	 * Occurences of a string over the limit will be ignored
	 * @see -m in MOSS-Documentation
	 * @param int $limit
	 * @return bool true
	 * @throws \Exception On invalid paramters
	 */
	public function setIngoreLimit($limit){
		if(is_int($limit) && $limit > 1){
			$this->options['m'] = (int)$limit;
			return true;
		}
		else{
			throw new \Exception("The limit needs to be greater than 1", 4);
		}
	}
	/**
	 * Set the comment for the request
	 * @see -s in MOSS-Documentation
	 * @param string $comment
	 */
	public function setCommentString($comment){
		$this->options['c'] = $comment;
		return true;
	}
	/**
	 * Set the number of results
	 * @see -n in MOSS-Documentation
	 * @param int $limit
	 */
	public function setResultLimit($limit){
		if(is_int($limit) && $limit > 1){
			$this->options['n'] = (int)$limit;
			return true;
		}
		else{
			throw new \Exception("The limit needs to be greater than 1", 5);
		}
	}

	/**
	 * Enable the Experimental Server
	 * @see -x in MOSS-Documentation
	 * @param bool $enabled
	 * @return bool true
	 * @throws \Exception
	 */
	public function setExperimentalServer($enabled){
		if(is_bool($enabled)){
			$this->options['x'] = (int)$enabled;
			return true;
		}
		else{
			throw new \Exception("Needs to be a boolean", 6);
		}
	}
	/**
	 * Add a file to the request
	 * @param string $file
	 */
	public function addFile($file){
		if(file_exists($file) && is_readable($file)){
			$this->files[] = $file;
			return true;
		}
		else{
			throw new \Exception("Can't find or read the file (".$file.")", 7);
		}
	}
	/**
	 * Add files by a wildcard
	 * @example addByWildcard("/files/*.c")
	 * @param string $path
	 */
	public function addByWildcard($path){
		foreach(glob($path) as $file){
			$this->addFile($file);
		}
	}

	/**
	 * Send the request to the server and wait for the response
	 * @return string
	 * @throws \Exception On failure
	 */
	public function send($output) {
		$socket = fsockopen($this->server, $this->port, $errno, $errstr);
		if(!$socket){
			throw new \Exception("Socket-Error: " . $errstr . " (" . $errno . ")", 8);
		} else {
//			echo "moss ". $this->userid."\n";
//			echo "directory " . $this->options['d']."\n";
//			echo "X " . $this->options['x']."\n";
//			echo  "maxmatches " . $this->options['m']."\n";
//			echo  "show " . $this->options['n']."\n";
//
//			//Language Check
//			echo "language " . $this->options['l']."\n";

			fwrite($socket, "moss ". $this->userid."\n");
			fwrite($socket, "directory " . $this->options['d']."\n");
			fwrite($socket, "X " . $this->options['x']."\n");
			fwrite($socket, "maxmatches " . $this->options['m']."\n");
			fwrite($socket, "show " . $this->options['n']."\n");

			//Language Check
			fwrite($socket, "language " . $this->options['l']."\n");
			$read = trim(fgets($socket));
			$output("Initial MOSS Response: $read\n\n");


			if($read === "no"){
				fwrite($socket, "end\n");
				fclose($socket);
				throw new \Exception("Unsupported language", 1);
			} else if($read !== 'yes') {
				throw new \Exception("MOSS is not responding", 1);
			}

			foreach($this->basefiles as $bfile){
				$this->uploadFile($socket, $bfile, 0);
			}
			$i = 1;
			foreach($this->files as $file){
				$this->uploadFile($socket, $file, $i);
				$i++;
			}

			$total = 0;
			$abort = false;
			foreach($this->raw as $raw) {
				$size = strlen($raw['text']);
				$file_name_fixed = str_replace(" ", "_", $raw['path']);
				$file_name_fixed = str_replace("/", "_", $file_name_fixed);
				$cmd = "file $i " . $raw['lang'] . " $size $file_name_fixed";
				$output("$cmd");

				$cmd .= "\n";

				if(fwrite($socket, $cmd) === false ||
					fwrite($socket, $raw['text'], $size) === false) {
					$output("Write to MOSS failed\n");
					$abort = true;
					break;
				}
				$i++;

				$total += strlen($cmd) + $size;
				$output(" $total\n");
			}

			$output("\nTotal: $total\n\n");

			if(!$abort) {
				fwrite($socket, "query 0 " . $this->options['c'] . "\n");
				$read = fgets($socket);
				if (substr($read, 0, 5) === "http:") {
					$output("<a href=\"$read\" target=\"moss\">$read</a>");
				} else {
					$output("Response: $read");
				}

				fwrite($socket, "end\n");
				fclose($socket);

				return $read;
			} else {
				return null;
			}
		}
	}
	/**
	 * Upload a file to the server
	 * @param  socket $handle A handle from fsockopen
	 * @param  string $file   The Path of the file
	 * @param  int $id     0 = Basefile, incrementing for every normal file
	 * @return void
	 */
	private function uploadFile($handle, $file, $id){
		$size = filesize($file);
		$file_name_fixed = str_replace(" ", "_", $file);
		fwrite($handle, "file ".$id." ".$this->options['l']." ".$size." ".$file_name_fixed."\n");
		fwrite($handle, file_get_contents($file));
	}
}

/// @endcond