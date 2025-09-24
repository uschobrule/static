<?php

namespace App\Http\Controllers;

use Response;
use View;

class JSONController extends Controller
{
	public function missingMethod($uri = array())
	{
		list($p1,$p2,$p3,$p4,$p5) = $this->parseuri($uri);

		$filename = $this->get_jsonfilename($p1,$p2,$p3,$p4,$p5);
                if (file_exists($filename)) {
                        $json = file_get_contents($filename);
                        return Response::json(array('json' => $json));
		} else {
			return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => 'Unknown Page', 'datatable' => ''));
		}
	}
}
