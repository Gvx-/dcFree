<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcFree for Dotclear 2.
 * Copyright (c) 2003-2013 Olivier Meunier & Association Dotclear (part of Clearbricks.)
 * Copyright © 2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/

class fileUnzip {
	protected $file_name;
	protected $compressed_list = array();
	protected $eo_central = array();

	protected $zip_sig   = "\x50\x4b\x03\x04"; # local file header signature
	protected $dir_sig   = "\x50\x4b\x01\x02"; # central dir header signature
	protected $dir_sig_e = "\x50\x4b\x05\x06"; # end of central dir signature
	protected $fp = null;

	protected $memory_limit = null;

	protected $exclude_pattern = '';

	public function __construct($file_name) {
		$this->file_name = $file_name;
	}

	public function __destruct() {
		$this->close();
	}

	public function close() {
		if($this->fp) {
			zip_close($this->fp);
			$this->fp = null;
		}

		if($this->memory_limit) {
			ini_set('memory_limit',$this->memory_limit);
		}
	}

	public function getList($stop_on_file=false,$exclude=false) {
		if(!empty($this->compressed_list)) {
			return $this->compressed_list;
		}

		if(!$this->loadFileListByEOF($stop_on_file,$exclude)) {
			if(!$this->loadFileListBySignatures($stop_on_file,$exclude)) {
				return false;
			}
		}

		return $this->compressed_list;
	}

	public function unzipAll($target) {
		$zip = zip_open($this->file_name);
		if(is_resource($zip)) {
			while(is_resource($zip_entry = zip_read($zip))) {
				$dest_file = $target.'/'.str_replace('\\', '/', zip_entry_name($zip_entry));
				if(substr($dest_file, -1, 1) == '/') {
					$this->testTargetDir($dest_file);
				} elseif(zip_entry_open($zip, $zip_entry, "r")) {
					@file_put_contents($dest_file, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)));
					zip_entry_close($zip_entry);
				} else {
					throw new Exception('Unzip: unknown error.');
				}
			}
			zip_close($zip);
		} else {
			throw new Exception('Unable to open file.');
		}
	}
	
	public function unzip($file_name, $target=false) {
		if(empty($this->compressed_list)) {
			$this->getList($file_name);
		}

		if(!isset($this->compressed_list[$file_name])) {
			throw new Exception(sprintf(__('File %s is not compressed in the zip.'),$file_name));
		}
		if($this->isFileExcluded($file_name)) {
			return;
		}
		$details =& $this->compressed_list[$file_name];

		if($details['is_dir']) {
			throw new Exception(sprintf(__('Trying to unzip a folder name %s'),$file_name));
		}

		if($target) {
			$this->testTargetDir(dirname($target));
		}
		
		$zip = zip_open($this->file_name);
		if(is_resource($zip)) {
			while(is_resource($zip_entry = zip_read($zip))) {
				$name = zip_entry_name($zip_entry);
				if($name != $file_name || substr($name, -1, 1) == '/') {
					continue;
				}
				
				if(zip_entry_open($zip, $zip_entry, "r")) {
					$content = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
					$this->putContent($content, $target);
					zip_entry_close($zip_entry);
				} else {
					throw new Exception('Unzip: unknown error.');
				}
			}
			zip_close($zip);
		} else {
			throw new Exception('Unable to open file.');
		}
		return $content;
	}
	
	public function getFilesList() {
		if(empty($this->compressed_list)) {
			$this->getList();
		}

		$res = array();
		foreach($this->compressed_list as $k => $v) {
			if(!$v['is_dir']) {
				$res[] = $k;
			}
		}
		return $res;
	}

	public function getDirsList() {
		if(empty($this->compressed_list)) {
			$this->getList();
		}

		$res = array();
		foreach($this->compressed_list as $k => $v) {
			if($v['is_dir']) {
				$res[] = substr($k,0,-1);
			}
		}
		return $res;
	}

	public function getRootDir() {
		if(empty($this->compressed_list)) {
			$this->getList();
		}

		$files = $this->getFilesList();
		$dirs = $this->getDirsList();

		$root_files = 0;
		$root_dirs = 0;
		foreach($files as $v) { if(strpos($v,'/') === false) { $root_files++; }}
		foreach($dirs as $v)  { if(strpos($v,'/') === false) { $root_dirs++;  }}

		if($root_files == 0 && $root_dirs == 1) {
			return $dirs[0];
		} else {
			return false;
		}
	}

	public function isEmpty() {
		if(empty($this->compressed_list)) {
			$this->getList();
		}

		return count($this->compressed_list) == 0;
	}

	public function hasFile($f) {
		if(empty($this->compressed_list)) {
			$this->getList();
		}

		return isset($this->compressed_list[$f]);
	}

	public function setExcludePattern($pattern) {
		$this->exclude_pattern = $pattern;
	}

	protected function fp() {
		if($this->fp === null) {
			$this->fp = zip_open($this->file_name);
		}

		if(!is_resource($this->fp)) {
			throw new Exception('Unable to open file.');
		}

		return $this->fp;
	}

	protected function isFileExcluded($f) {
		if(!$this->exclude_pattern) {
			return false;
		}

		return preg_match($this->exclude_pattern,$f);
	}

	protected function putContent($content, $target=false) {
		if($target) {
			$r = @file_put_contents($target, $content);
			if($r === false) {
				throw new Exception(__('Unable to write destination file.'));
			}
			files::inheritChmod($target);
			return true;
		}
		return $content;
	}

	protected function testTargetDir($dir) {
		if(is_dir($dir) && !is_writable($dir)) {
			throw new Exception(__('Unable to write in target directory, permission denied.'));
		}

		if(!is_dir($dir)) {
			files::makeDir($dir, true);
		}
	}

	protected function uncompress($content, $mode, $size, $target=false) {
		throw new Exception(sprintf(__("Function %s isn't supported by dcFree plugin."), 'fileUnzip->uncompress'));
	}

	protected function loadFileListByEOF($stop_on_file=false, $exclude=false) {
		$fp = $this->fp();
		while(is_resource($zip_entry = zip_read($fp))) {
			$name = str_replace('\\', '/', zip_entry_name($zip_entry));
			if($exclude && preg_match($exclude, $name)) {
				continue;
			}

			$this->compressed_list[$name]['file_name']				= $name;
			$this->compressed_list[$name]['is_dir']					= substr($name, -1, 1) == '/';
			$this->compressed_list[$name]['compression_method']		= zip_entry_compressionmethod($zip_entry);
			$this->compressed_list[$name]['version_needed']			= null;
			$this->compressed_list[$name]['lastmod_datetime']		= null;
			$this->compressed_list[$name]['crc-32']					= null;
			$this->compressed_list[$name]['compressed_size']		= zip_entry_compressedsize ($zip_entry);
			$this->compressed_list[$name]['uncompressed_size']		= zip_entry_filesize($zip_entry);
			$this->compressed_list[$name]['extra_field']			= null;
			$this->compressed_list[$name]['contents_start_offset']	= null;
			
			zip_entry_close($zip_entry);

			if(strtolower($stop_on_file) == strtolower($name)) {
				break;
			}
		}
		$this->close();
		return true;
	}
	
	protected function loadFileListBySignatures($stop_on_file=false, $exclude=false) {
		return $this->loadFileListByEOF($stop_on_file, $exclude);
	}

	protected function getFileHeaderInformation($start_offset=false) {
		throw new Exception(sprintf(__("Function %s isn't supported by dcFree plugin."), 'fileUnzip->getFileHeaderInformation'));
	}

	protected function getTimeStamp($date,$time) {
		$BINlastmod_date = str_pad(decbin($date), 16, '0', STR_PAD_LEFT);
		$BINlastmod_time = str_pad(decbin($time), 16, '0', STR_PAD_LEFT);
		$lastmod_dateY   = bindec(substr($BINlastmod_date,  0, 7))+1980;
		$lastmod_dateM   = bindec(substr($BINlastmod_date,  7, 4));
		$lastmod_dateD   = bindec(substr($BINlastmod_date, 11, 5));
		$lastmod_timeH   = bindec(substr($BINlastmod_time,   0, 5));
		$lastmod_timeM   = bindec(substr($BINlastmod_time,   5, 6));
		$lastmod_timeS   = bindec(substr($BINlastmod_time,  11, 5)) * 2;

		return mktime($lastmod_timeH, $lastmod_timeM, $lastmod_timeS, $lastmod_dateM, $lastmod_dateD, $lastmod_dateY);
	}

	protected function cleanFileName($n) {
		$n = str_replace('../','',$n);
		$n = preg_replace('#^/+#','',$n);
		return $n;
	}

	protected function memoryAllocate($size) {
		$mem_used = function_exists('memory_get_usage') ? @memory_get_usage() : 4000000;
		$mem_limit = @ini_get('memory_limit');
		if($mem_used && $mem_limit) {
			$mem_limit = files::str2bytes($mem_limit);
			$mem_avail = $mem_limit-$mem_used-(512*1024);
			$mem_needed = $size;

			if($mem_needed > $mem_avail) {
				if(@ini_set('memory_limit',$mem_limit+$mem_needed+$mem_used) === false) {
					throw new Exception(__('Not enough memory to open file.'));
				}

				if(!$this->memory_limit) {
					$this->memory_limit = $mem_limit;
				}
			}
		}
	}
}
