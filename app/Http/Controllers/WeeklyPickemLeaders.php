<?php

namespace App\Http\Controllers;
use Response;

class WeeklyPickemLeaders extends JSONController
{	
	public function leaders($gender,$division,$season,$game_type)
	{
		# parse_uri
		$template = "leaders_template";
                $full_season = $this->get_current_full_season();
                $gender = "m";
                $code = "p10";
                $division = "I";
		$week = "all";
                $full_week = "Week $week";
                if ($week == "all") { $full_week = "Season";$week="season";}

                $season = $this->get_season($full_season);

		# load conf
		$page_title = "Men's D1 Random Pickem Leaderboard (".$full_week.")";
		
		# composite is all in one file
		$filename = config('services.JSON_DIR')."pickem/rank-".$season.".json";
		
		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$templatec = str_replace("-","_",$template);

			if (property_exists($json_obj,"p10_I_m")) {
				list ($data,$datatable,$meta_data,$page_title) = $this->$templatec($template,$json_obj,$code,$page_title,$gender,$division,$full_season,$week);
				return Response::json(array('json' => $data, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
			}
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));		
	}
//	public function leaders($template,$json,$code,$page_title,$gender,$division,$full_season,$week){
	public function leaders_template($template,$json,$code,$page_title,$gender,$division,$full_season,$week){
		
		$datatable['dt1']['sub_title'] = '';
		$meta_data = [];
		$meta_data["paging"] = 1;
		$meta_data["bFilter"] = 1;
		$meta_data["bInfo"] = 0;
		
		$meta_data["order"] = [];
		array_push($meta_data["order"], "0");
		array_push($meta_data["order"], "asc");

		$columns = [];
		$columns[0]["title"] = "Rnk";
		$columns[1]["title"] = "User";
		$columns[2]["title"] = "Pts";
		$columns[3]["title"] = "Games Pick Pct*";
		$columns[4]["title"] = "Pct Currect";

		$meta_data["columns"] = $columns;

		$d = [];
		$ind = 0;

		foreach ( $json->p10_I_m->res->$week AS $user_id) {
			$rec = [];

			$rec[0] = $json->p10_I_m->user->$user_id->week->$week->rnk;
			$rec[1] = '<a href="/user/'.$json->p10_I_m->user->$user_id->user_login.'">'.$json->p10_I_m->user->$user_id->user_login.'</a>';
			$rec[2] = $json->p10_I_m->user->$user_id->week->$week->cur;
			$rec[3] = $json->p10_I_m->user->$user_id->week->$week->user_pick_per;
			$rec[4] = $json->p10_I_m->user->$user_id->week->$week->cur_per;

			$ind++;
			array_push($d,$rec);
		}

		$data['dt1']['data']['data'] = $d;
		$data['dt1']['data']['meta_data'] = $meta_data;

		$data['dt1']['meta_data'] = $meta_data;
		$data['dt1']['sub_title'] = "";
		
		return array($data,$datatable,$meta_data,$page_title);	
	}
}
