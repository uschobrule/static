<?php

namespace App\Http\Controllers;

use Response;
use View;

class AppRankingsController extends AppController
{	
    public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
		
		$template =  $parts[0];
		$p2 = explode("-",$parts[0]);
		$c2 = count($p2);
		
		$gender = "";
		$full_gender = "";
		$division = "";
		$date = "current";
		$refreshTime = 0;
		
		if($count > 1) {
			$p2 = explode("-",$parts[1]);
			$c2 = count($p2);
			$full_gender = $p2[2];
			$gender = $this->get_gender($p2[2]);
			$division = strtoupper($p2[1]);	
			if ($count > 2) {
				$date = $parts[2];
				if ($count > 4) {
       	                         	$refreshTime = $parts[3];
	                        }
			}
		}

		if ($date == "") {
			$season = $this->get_season("");
			$date = substr($season,0,4)."-".substr($season,-4);
		}

		$templatec = str_replace("-","_",$template);
		
		return array($templatec,$gender,$division,$date,$full_gender,$refreshTime);
	}
	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($template,$gender,$division,$date,$full_gender,$refreshTime) = $this->parseuri($uri);
		return $this->$template($gender,$division,$date,$full_gender,$refreshTime);
	}
	public function poll($division,$full_gender,$date,$refreshTime,$req)
	{

		$gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);

		# poll is all in one file
		$filename = config('services.JSON_DIR')."/site/poll.json";
		$page_title = "";

		if (file_exists($filename)) {
                        $stat = stat($filename);
			if ($refreshTime != 0 && $refreshTime == $stat['mtime']) {
				return Response::json(array('success' => 2, 'refreshTime' => $refreshTime));
			}
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
	
			$data = [];
	
			$polldate = $json_obj->poll_data->$gender->$division->$date->PollDate;
			$pd = $json_obj->poll_data->$gender->$division->$polldate;
			$tmp_title = "USCHO ".$pd->PollName." - ".$pd->full_poll_date;
			$page_title = preg_replace( "/Division /", "D", $tmp_title );
			$polldata = $json_obj->data->$gender->$division->$polldate;
			
			foreach ($polldata->data as $rec) {
				$rec->prev_rnk = $this->get_team_rank($json_obj,$gender,$division,$rec->shortname,$pd->PrePollDate);
				if ($rec->prev_rnk == "") {$rec->prev_rnk = "NR";}
			}

			$team_code = [];
			$data = [];
			$team_col = 1;
			$team_map_data = $this->load_team_map_data();
                        $team_map = $team_map_data[$gender];

			foreach ($polldata->data as $rec) {
                                $res = array();
				
				array_push($res,$rec->rnk);
				array_push($res,$rec->shortname);
				$first_pv = $rec->first_pv;
				if ($first_pv == 0) {$first_pv = "";}
				array_push($res,$first_pv);
				array_push($res,$rec->record);
				array_push($res,$rec->pts);
	
				$rec->prev_rnk = $this->get_team_rank($json_obj,$gender,$division,$rec->shortname,$pd->PrePollDate);
                                if ($rec->prev_rnk == "") {$rec->prev_rnk = "NR";}
				array_push($res,$rec->prev_rnk);
;	
                                array_push($data,$res);

                                if ($team_col >= 0 ) {
                                        $team = $team_map[$res[$team_col]];
                                        array_push($team_code,['teamCode' => $team->code,'confCode' => $team->conf_code]);
                                }
                        }

			# load conf
                	$columns = array(0,1,2,3,4,5);
			$prehead = array("","","","","","");
			$weights = array("2.0f","5.0f","2.0f","3.0f","2.0f","2.0f");
			$oppcolumns = array(0,0,0,0,0,1);
			$footer = $polldata->other;
			$header = array("Rnk","Team","First Place","Rec","Pts","Prev");

			return $this->response_data($page_title, $columns, $weights,$prehead,$footer,$data,$header,$stat,[],$oppcolumns,$team_code);
		}
		return Response::json(array('success' => 0, 'html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}

	public function rpi($division,$full_gender,$date,$refreshTime,$req)
	{

		$gender = $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
                $season = $this->get_season("");
		$full_season = substr($season,0,4)."-".substr($season,-4);

		# composite is all in one file
		$season = $this->get_season($full_season);
		$filename = $this->get_jsonfilename("ranking","npi",$gender,$division,$season,"sc");
		//echo $filename;
		
		$data = [];
		
		$page_title = $this->get_full_gender($gender)."'s D".$division." National Collegiate Percentage (NPI) Index";
	
		if (file_exists($filename)) {
			$stat = stat($filename);
			if ($refreshTime != 0 && $refreshTime == $stat['mtime']) {
                        	return Response::json(array('success' => 2, 'refreshTime' => $refreshTime));
                        }

			$columns = array(0,1,5,6,7,8);
       		        $prehead = array();
                	$weights = array("2.0f","5.0f","1.0f","2.0f","2.0f","2.0f");
                	$oppcolumns = array(0,0,0,0,1,1);

                	$team_map_data = $this->load_team_map_data();
                	$team_map = $team_map_data[$gender];

			list($data,$header,$stat,$team_code) = $this->app_data($filename,$columns,1,$team_map);
			$header[2] = "Adj RPI";
			return $this->response_data($page_title, $columns, $weights,$prehead,"",$data,$header,$stat,[],$oppcolumns,$team_code);
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}
	public function pairwise_rankings($division,$full_gender,$date,$refreshTime,$req)
	{

		$gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
		$full_season = substr($season,0,4)."-".substr($season,-4);

		# composite is all in one file
		$season = $this->get_season($full_season);
		$filename = $this->get_jsonfilename("ranking","npi",$gender,$division,$season,"sc");
		// echo $filename;
		
		$data = [];
		
		$page_title = $this->get_full_gender($gender)."'s D".$division." National Collegiate Percentage (NPI) Index";
		
		if (file_exists($filename)) {
	                $stat = stat($filename);
                        if ($refreshTime != 0 && $refreshTime == $stat['mtime']) {
                                return Response::json(array('success' => 2, 'refreshTime' => $refreshTime));
                        }

			$columns = array(0,1,2,3,4,6);
                	$prehead = array();
                	$weights = array("2.0f","5.0f","1.0f","2.0f","2.0f","2.0f");
                	$oppcolumns = array(0,0,0,0,1,1);

                	$team_map_data = $this->load_team_map_data();
                	$team_map = $team_map_data[$gender];

			list($data,$header,$stat,$team_code) = $this->app_data($filename,$columns,1,$team_map);
			$header[2] =  "PW";
                        return $this->response_data($page_title, $columns, $weights,$prehead,"",$data,$header,$stat,[],$oppcolumns,$team_code);
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}
}
