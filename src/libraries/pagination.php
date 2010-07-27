<?php

/*
Pagination Library

Copyright 2010 Serverboy Software; Matt Basta

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

define('PAGE_STYLE_ALL', 0);
define('PAGE_STYLE_FIRST_TEN', 1);
define('PAGE_STYLE_ADJACENT_THREE', 2); // Default
define('PAGE_STYLE_ADJACENT_FIVE', 3);

class lib_pagination {
	
	function exec($data) {
		$data;
		return false;
	}
	
	function getPage($id = '') {
		
		if(!empty($id))
			$id .= '_';
		
		if(empty($_REQUEST[$id . 'p']))
			$page = 1;
		else
			$page = intval($_REQUEST[$id . 'p']);
		
		return $page;
		
	}
	function getOffset($perpage, $id = '') {
		
		$page = $this->getPage($id) - 1;
		
		return $perpage * $page;
		
	}
	
	// URL should be a string with %s for the page and %id for the id.
	function buildPaginationControls($url, $itemcount, $perpage, $currentpage = 1, $id = '', $style = 2, $firstlast = false) {
		
		if($itemcount <= $perpage) {
			return "";
		}
		
		if($perpage > 0)
			$pages = ceil($itemcount / $perpage);
		else
			$pages = ceil($itemcount / 20);
		
		$output = '';
		if($currentpage > 1) {
			if($firstlast)
				$output .= '<li class="pag_first"><a href="' . $this->buildURL($url, $id, 1) . '">First</a></li>';
			
			$output .= '<li class="pag_previous"><a href="' . $this->buildURL($url, $id, $currentpage - 1) . '">Previous</a></li>';
		}
		
		// Default for PAGE_STYLE_ALL
		$start = 1;
		$end = $pages; // Some old code says to use $page + 1. No idea why, but it might fix a bug in the future.
		
		switch($style) {
			case PAGE_STYLE_FIRST_TEN:
				$start = max(1, $currentpage - 1);
				$end = min($pages, $currentpage + 1);
				break;
			case PAGE_STYLE_ADJACENT_FIVE:
				$start = max(1, $currentpage - 5);
				$end = min($pages, $currentpage + 5);
				break;
			case PAGE_STYLE_ADJACENT_THREE:
			default:
				$start = max(1, $currentpage - 3);
				$end = min($pages, $currentpage + 3);
				break;
		}
		
		if($style == PAGE_STYLE_FIRST_TEN) {
			
			for($i = 1; $i < $page - 1 && $i < min($pages + 1, 11); $i++) {
				
				$output .= $this->buildPage($url, $i, $pages, $currentpage, $id);
				
			}
			
		}
		
		for($i = $start; $i < $end + 1; $i++) {
			$output .= $this->buildPage($url, $i, $pages, $currentpage, $id);
		}
		
		if($currentpage < $pages) {
			if($firstlast)
				$output .= '<li class="pag_last"><a href="' . $this->buildURL($url, $id, $pages) . '">Last</a></li>';
			
			$output .= '<li class="pag_next"><a href="' . $this->buildURL($url, $id, $currentpage + 1) . '">Next</a></li>';
		}
		
		return $output;
		
	}
	
	function buildPage($url, $page, $pagecount, $currentpage, $id = '') {
		$classes = array( 'pag_num' );
		
		if($page == 1) $classes[] = 'first';
		if($page == $currentpage) $classes[] = 'current';
		if($page % 2 == 0) $classes[] = 'odd';
		if($page == $pagecount) $classes[] = 'last';
		
		return '<li class="' . implode(" ",$classes) . '"><a href="' . $this->buildURL($url, $id, $page) . "\">$page</a></li>";
		
	}
	
	function buildURL($url, $id, $page, $escape = true) {
		$url = str_replace('%s', $page, $url);
		$url = str_replace('%id', $id, $url);
		if($escape)
			$url = htmlentities($url);
		return $url;
	}
	
}
