<?php

define("ROOT", dirname(__FILE__) . DIRECTORY_SEPARATOR);

//Require required requirements
require('helper/BakaInterface.php');
require('helper/BakaParser.php');
require('helper/BakaCache.php');

//Prepare instances
$baka = new BakaInterface();
$aho  = new BakaParser();

//Determine action
$action = isset($_GET['action']) ? $_GET['action'] : "list";
$output = array('success' => true);


switch($action) {
	case 'list':
		$sidebar = $baka->getSource("MediaWiki:Sidebar");
		$projects = $aho->getProjects($sidebar);

		$output['data'] 	= array('projects' => $projects);
		$output['template'] = 'projects';
		break;

	case 'project':
		$page = isset($_GET['page']) ? $_GET['page'] : null;
		if($page) { 
			$project = $baka->getHTML($page);
			$pPage   = $aho->getProjectPage($project);
			if($pPage) {
				$pPage['page'] = $page;
				$output['data'] 	= $pPage;
				$output['template'] = 'project_page';
			} else {
				$output['success'] = false;
			}
		} else {
			$output['success'] = false;
		}
		break;

	case "chapter":
		$page = isset($_GET['page']) ? $_GET['page'] : null;
		if($page) { 
			$html = $baka->getHTML($page);
			$html = $aho->getChapter($html);
			header('Content-Type: text/html; charset=utf-8'); 
			die($html);
		} else {
			$output['success'] = false;
		}
		break;

	default:

		break;
}

header('Content-Type: application/json; charset=utf-8'); 
echo json_encode($output);