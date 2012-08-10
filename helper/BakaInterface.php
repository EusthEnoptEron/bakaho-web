<?php

/**
 * Interface to the BakaTsuki website. Is in charge of fetching the pages.
 */
class BakaInterface {
	/**
	 * Link to Baka-Tsuki.
	 */
	const URL = "http://www.baka-tsuki.org/project/index.php?";

	/**
	 * Fetches a page.
	 * @param  [type] $url    [description]
	 * @param  [type] $fields [description]
	 * @return [type]         [description]
	 */
	protected function getPage($url, $fields = null) {
		$fields_string = '';

		if($fields) {
			//url-ify the data for the POST
			foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
			rtrim($fields_string,'&');
		}

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt($ch,CURLOPT_URL,$url);
		if($fields) {
			curl_setopt($ch,CURLOPT_POST,count($fields));
			curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);
		}

		//execute post
		ob_start();
		curl_exec($ch);
		$result = ob_get_clean();
		//close connection
		curl_close($ch);

		return $result;
	}

	/**
	 * Fetches the HTML of a Baka-Tsuki page.
	 * @param  string  $page  Name of the page.
	 * @param  boolean $force If forced, this will not return a cached version.
	 * @return string        HTML.
	 */
	public function getHTML($page, $force = null) {
		require_once(ROOT . "lib" . DIRECTORY_SEPARATOR . "simple_html_dom.php");

		$text;
		if($force || 
		  !($text = BakaCache::get($page, BakaCache::HTML))) {

			$html = $this->getPage(self::URL . 'title=' . urlencode($page));
			$html = str_get_html($html);

			$text = (string)$html->find('#content', 0);


			BakaCache::set($page, BakaCache::HTML, $text);
		}
		return $text;
	}

	/**
	 * Fetches the WikiCode of a Baka-Tsuki page.
	 * @param  string  $page  Name of the page.
	 * @param  boolean $force If forced, this will not return a cached version.
	 * @return [type]         Wikitext.
	 */
	public function getSource($page, $force = null) {
		$text;
		if($force || 
		  !($text = BakaCache::get($page, BakaCache::SOURCE))) {
			//Set up needed pages.
			$fields = array(
				'catname'=>'',
				'pages'=>$page,
				'curonly'=>1,
				'wpDownload'=>1,
				'title'=>"Special:Export",
				'action'=>'submit'
				);

			$xml = $this->getPage(self::URL . 'title=Special:Export&action=submit', $fields);
			$xml = simplexml_load_string(str_replace('xmlns=', 'ns=', $xml));

			$rev = $xml->page->revision->id;
			$text = $xml->page->revision->text;

			BakaCache::set($page, BakaCache::SOURCE, $text);
		}
		return $text;
	}
}