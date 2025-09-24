<?php

namespace App\Http\Controllers;

use Response;
use View;

class AppController extends JSONController
{	
	public function format_app_data($filename,$page_title, $columns, $weights,$prehead,$footer,$oppcolumns,$team_col,$team_map) {

		list($data,$header,$stat,$team_code) = $this->app_data($filename,$columns,$team_col,$team_map);

		return $this->response_data($page_title, $columns, $weights,$prehead,$footer,$data,$header,$stat,[],$oppcolumns,$team_code);
	}

	public function response_data($page_title, $columns, $weights,$prehead,$footer,$data,$header,$stat, $info,$oppcolumns,$team_code) {
		if (count($data)) {
                        return Response::json(array('success' => 1, 'data' => $data, 'header' => $header,  'info' => $info, 'weights' => $weights, 'page_title' => $page_title, 'updated' => "Last Updated: ".date("F d, Y, h:i:s A",$stat['mtime']), 'footer' => $footer, 'prehead' => $prehead, 'oppcolumns' => $oppcolumns, 'team_code' => $team_code, 'refreshTime' => $stat['mtime']));
                }
                return Response::json(array('success' => 0, 'message' => "Currently not available", 'data' => [], 'header'=> [],  'info' => [], 'weights' => [], 'page_title' => $page_title, 'footer' => $footer, 'prehead' => $prehead, 'oppcolumns' => $oppcolumns, 'team_code' => []));
	}

	public function app_data($filename,$columns,$team_col,$team_map) {

		$data = [];
                $header = [];
		$stat = "";

               	if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_data = json_decode($json,true);

                        $src_data = $json_data['data'];
                        if(array_key_exists("clean_data",$json_data)) {$src_data = $json_data['clean_data']['data'];}

                        foreach ($columns AS $ind => $value) {
                                array_push($header,$json_data['meta_data']['columns'][$value]['title']);
                        }

                	$team_code = [];

                        foreach ($src_data AS $ind => $rec) {
                                $res = array();
                                foreach ($columns AS $ind => $value) {
                                        array_push($res,$rec[$value]);
                                }
                                array_push($data,$res);
			
				if ($team_col >= 0 ) {	
					$team = $team_map[$res[$team_col]];
                        		array_push($team_code,['teamCode' => $team->code,'confCode' => $team->conf_code]);
				}
                        }
                }

		return array($data,$header,$stat,$team_code);
        }

	// hard coded to ly delete when go live
        public function get_season($full_season)
        {
		return parent::get_season($full_season);
                //return "20182019";
        }
}
