<?php

class BakaParser {


	/**
	 * Returns a list of projects.
	 * @param  [type] $sidebar [description]
	 * @return [type]          [description]
	 */
	public function getProjects($sidebar) {
		$start = strpos($sidebar, '* Light Novels');

		$lines = explode("\n", substr($sidebar, $start));
		//Remove * Light Novels
		array_shift($lines);
		$projects = array();

		foreach($lines as $line) {
			if(preg_match('/^\*\*\s([^\s]+?)\|(.+)$/', $line, $match)) {
				$projects[] = array(
					'name' => $match[2],
					'page' => $match[1]
					);
			} elseif(substr($line, 0, 2) == '* ') {
				break;
			}
		}

		return $projects;
	}
	protected function clean($string) {
		$string = preg_replace('/<.+?>/', '', $string);
		$string = preg_replace('/\[.+?\]/', '', $string);
		$string = preg_replace("/^(.+?)[(\[].*$/", '$1', $string);

		$string = trim($string);
		return $string;
	}

	public function getChapter($html) {
		$html = str_get_html($html);

		
		foreach($html->find("#siteSub, #catlinks, #jump-to-nav, .printfooter, .editsection, .thumbcaption") as $el) {
			$el->outertext = '';
		}

		foreach($html->find("img") as $img) {
			$img->src = "http://www.baka-tsuki.org" . $img->src;
		}

		$preview = false;
		foreach($html->find("table") as $table) {
			if((isset($table->id) && $table->id == 'toc') 
				|| (isset($table->class) && preg_match('/collapsible/', $table->class))
				|| preg_match("/forward/i", $table->innertext)) {
				$table->outertext = '';
			} elseif(preg_match('/Preview_symbol\.gif/', $table->innertext)) {
				$table->next_sibling()->next_sibling()->outertext = '';
				$table->outertext = '';
				$preview = true;
			} 
		}
		if($preview)
			$html->innertext = '<div class="redButton">This is a preview</div>' . $html->innertext;

		return $html->innertext;
	}
	public function getProjectPage($project) {
		$p = array();
		$html = str_get_html($project);
		$p['image'] = $html->find(".thumb img", 0)->src;
		$p['volumes'] = array();
		$p['title']   = $html->find('#firstHeading', 0)->innertext;
		$p['synopsis'] = '';

		foreach($html->find("h2") as $h2) {
			$title = $h2->innertext;
			if(preg_match('/( by |Full text|'.preg_quote($p['title'], '/').')/i', $title)) {
				//Okay, can be considered a volume.
				$el = $h2;
				$vname = $title = $this->clean($title);

				while($el = $el->next_sibling()) {
					switch($el->tag) {
						case 'dl':
						case 'ul':
						case 'div':
							$v = array();
							//Okay, find links.
							$effective = 0;
							foreach($el->find("a") as $a) {
								$exists = true;
								if(isset($a->class) && preg_match("/\bnew\b/", $a->class)) {
									$exists = false;
								}
								$name = $a->innertext;
								$page = preg_replace('/^.+?title=/', '', $a->href);
								$v[$page] = $name;

								if($exists) $effective++;
							}
							if($effective > 1)
								$p['volumes'][$vname] = $v;
							break;

						case 'h3':
							$vname = $this->clean($el->innertext);
							break;
						case 'h2':
							break 2;
					}
				}
			} elseif(preg_match('/synopsis/i', $title)) {
				$synopsis = '';
				$el = $h2;
				while($el = $el->next_sibling()) {
					if($el->tag != 'h2') {
						$synopsis .= $el;
					} else {
						break;
					}
				}

				$p['synopsis'] = $synopsis;
			}
		}

		return $p;
		/*
		//Get image
		if(preg_match('/\[\[Image:(.+?)(\||\])/i', $project, $match)) {
			$md5 = md5($match[1]);
			//Folder is constructed by the md5 value of the file name
			$p['image'] = substr($md5,0,1) . "/" . substr($md5, 0, 2) . '/' . $match[1];
		}
		$p['volumes'] = array();

		if(preg_match_all('/^==([^=]+)( by |Full Text).+==$/m', $project, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
			//OKAY
			foreach($matches as $match) {
				$offset = $match[0][1] + strlen($match[0][0]);
				$context = preg_split('/^==[^=]/m', substr($project, $offset));
				$context = $context[0];
				//Okay, we have created our context.
				$volumes = preg_split('/^===([^(\[=]+).*?===$/m', $context, -1, PREG_SPLIT_DELIM_CAPTURE);

				$vname = trim(preg_replace("/^(.+?)[(\[].*$/", '$1', $match[1][0]));
				if(!isset($p['title'])) {
					$p['title'] = $vname;
				}

				foreach($volumes as $volume) {
					$volume = trim($volume);
					if($volume) {
						if(strpos($volume, '[[') === FALSE) {
							$vname = $volume;
						} else {
							$v = array();
							//okay, it's a chapter list.
							preg_match_all('/\[\[(.+?)\|(.+?)\]\]/', $volume, $chapters, PREG_SET_ORDER);
							foreach($chapters as $chapter) {
								$v[trim($chapter[1])] = trim($chapter[2]); 
							}
							$p['volumes'][$vname] = $v;
						}
					}
				}
			}
		
			return $p;
		} else {
			return null;
		}*/

	}
}