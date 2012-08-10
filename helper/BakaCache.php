<?php

class BakaCache {
	const HTML = 'html';
	const SOURCE = 'source';


	public static function get($page, $mode, $expire = null) {
		if($mode != self::HTML && $mode != self::SOURCE) {
			throw new Exception("Invalid mode.");
		}
		
		$path = self::getPath($page, $mode);

		if(file_exists($path)) {
			$modified = filemtime($path);
			if($expire && (time() - $modified) > $expire) {
				return NULL;
			}
			return file_get_contents($path);
		} else {
			return NULL;
		}
	}

	public static function set($page, $mode, $text) {
		if($mode != self::HTML && $mode != self::SOURCE) {
			throw new Exception("Invalid mode.");
		}
		
		$path = self::getPath($page, $mode);

		file_put_contents($path, $text);
		return true;
	}



	private static function getPath($page, $mode) {
		$parts = explode(":", $page);

		$path = ROOT . 'data' . DIRECTORY_SEPARATOR . $mode;
		if(!file_exists($path))
			mkdir($path);

		$file = array_pop($parts);

		foreach($parts as $part) {
			$part = substr(md5($part), 0, 2);
			$path .= DIRECTORY_SEPARATOR . $part;
			if(!file_exists($path))
				mkdir($path);
		}
		$file = preg_replace('/\W/', '', $file);
		$ending = $mode == self::HTML ? ".html" : ".txt";
		return $path . DIRECTORY_SEPARATOR . $file . $ending;
	}

}