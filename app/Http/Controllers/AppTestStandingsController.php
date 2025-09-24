<?php

namespace App\Http\Controllers;
use Response;

class AppTestStandingsController extends AppController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
		$gender = "m";
		$division = "I"; 
		$code = $parts[1];

		$refreshTime = 0;
		$divgen = explode("-",$parts[0]);
                $count2 =  count($divgen);
                if ($count2 > 0) {
                	$division = $divgen[1];
                        $gender = $this->get_gender($divgen[2]);
                }

		if ($count > 3) {
                	$refreshTime = $parts[2];
                }

		$template = "standings";

		$full_season = $this->get_current_full_season();
		$season = $this->get_season($full_season);

		return array($gender,strtoupper($division),$season,$code,$full_season,$refreshTime);
	}

	public function standings($division,$full_gender,$code,$refreshTime,$req)
	{

		$division = strtoupper($division);
		$gender = $this->get_gender($full_gender);
		$full_season = $this->get_current_full_season();
		$season = $this->get_season($full_season);

		# kludge for Northeast 10 n10
                if ($code == "n10") {$division = "II";}
		$confdata = $this->conf_map($code);
		$filename = $this->get_jsonfilename("standings",$confdata->code,$gender,$division,$season,"");

		$stat = stat($filename);
		if ($refreshTime != 0 && $refreshTime == $stat['mtime']) {
			return Response::json(array('success' => 2, 'refreshTime' => $refreshTime));
		}

		$pattern = "/Hockey|Independent/";
		$hockey = " Hockey";
                if (preg_match($pattern,$confdata->shortname)) $hockey = "";
		
                $page_title = $confdata->shortname." ".$this->get_full_gender($gender)."'s".$hockey." Standings: ".$full_season;

		# load conf
                $columns = array(0,2,3,4,5);
                $prehead = array("","Conf","","","","");
		$weights = array("2.0f",".25f","5.0f","1.0f","2.0f","1.0f");
		$oppcolumns = array(0,1,0,0,1,1);

		$team_map_data = $this->load_team_map_data();
                $team_map = $team_map_data[$gender];

		list($data,$header,$stat,$team_code) = $this->app_data($filename,$columns,0,$team_map);
		
		$polldata = $this->poll_data();
		$dm = ['I' => 'I', 'II' => 'III', 'III' => 'III'];

		$tie_breaker = [];
		$tie_breaker_file = $this->get_jsonfilename("standings","tie_breaker",$gender,$division,$season,[]);
		if (file_exists($tie_breaker_file)) {
			 $tbjson = file_get_contents($tie_breaker_file);
                         $tie_breaker = json_decode($tbjson,true);
		}

		$orig_team_order = [];
                $ind =0;  

		$newdata = [];
		foreach ($data AS $ind => $rec) {
		        $orig_team_order[$rec[0]] = $ind;
                        $ind++;

                        $team = $this->team_map($gender,$rec[0]);

                        $rnk = $this->get_team_rank($polldata,$gender,$dm[$division],$rec[0],"current");
                        if ($rnk > 0) {$rec[0] = "(".$rnk.") ".$rec[0];}
                        array_push($newdata,$rec);
                }

		$sdata = [];
                if (array_key_exists($confdata->code,$tie_breaker)) {
                	$tbdata = [];
                        foreach ($tie_breaker[$confdata->code] AS $tname) {
                        	array_push($tbdata,$newdata[$orig_team_order[$tname]]);
                        }
                        $sdata = $tbdata;
                } else {
                	$sdata = $newdata;
                }

		return $this->response_data($page_title, $columns, $weights,$prehead,"",$sdata,$header,$stat,[],$oppcolumns,$team_code);
	}
}
