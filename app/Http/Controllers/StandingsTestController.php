<?php

namespace App\Http\Controllers;
use Response;

class StandingsTestController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
		$gender = "";
		$full_season = $this->get_current_full_season();
		$code = "";
		$pattern = "/^division-/";
		
		if (preg_match($pattern,$parts[0])) {
			$divgen = explode("-",$parts[0]);
			$count2 =  count($divgen);
			if ($count2 > 0) {
				$division = $divgen[1];
				$gender = $this->get_gender($divgen[2]);;
			}
		} else {
			if ($count > 0) $sitecode = $this->get_confsitecode($parts[0]);
			$division = $sitecode['division'];
			$gender = $sitecode['gender'];
			$code = $sitecode['code'];
		}
		
		$template = "standings";

		if ($count > 1) $full_season = $parts[1];

		$season = $this->get_season($full_season);

		return array($gender,strtoupper($division),$season,$code,$full_season);
	}

	public function standingsall($division,$full_gender,$full_season="")
	{
		$division = strtoupper($division);
		$gender = $this->get_gender($full_gender);
		return $this->standings($division,$gender,$full_season,"");
	}

	public function standingsconf($conf,$full_season="")
	{      
		$sitecode = $this->get_confsitecode($conf);
                $division = $sitecode['division'];
		$gender = $sitecode['gender'];
                $code = $sitecode['code'];
	
                return $this->standings($division,$gender,$full_season,$code);
        }

        public function standings($division,$gender,$full_season="",$code="")
	{
		$season = $this->get_season($full_season);

		# use all teams including defunct 
		$this->load_teamall_map_data();
		$tm = $this->team_map_data;
		//var_dump($tm['w']);

		$polldata = $this->poll_data();

		# load conf
		$conf = $this->get_confs($gender,$division);
		$data = [];
		$datatable = [];
		$page_title = $this->get_full_gender($gender)."'s Division ".$division." Hockey Standings: ".$full_season;

		# attempt to load json files
		$i=0;
		ksort($conf);
		$pattern = "/^Hockey/";
		$dm = ['I' => 'I', 'II' => 'III', 'III' => 'III'];

		$tie_breaker = [];
		$tie_breaker_file = $this->get_jsonfilename("standings","tie_breaker",$gender,$division,$season,[]);
		if (file_exists($tie_breaker_file)) {
			 $tbjson = file_get_contents($tie_breaker_file);
                         $tie_breaker = json_decode($tbjson,true);
		}

		# determine poll date
		$poll_date = "current";
		if ($this->get_current_season() != $season) {
			if (property_exists($polldata->poll_data->poll_season,$season)) {
				$poll_date = $polldata->poll_data->poll_season->$season->$gender->$division->max_date;
			} else {
				$poll_date = "NA";
			}
		}

		foreach ($conf as $key => $rec) {
			if ($code == "" or $rec['code'] == $code) {
				$filename = $this->get_jsonfilename("standings",$rec['code'],$gender,$division,$season,$rec);
				if (file_exists($filename)) {
					$json = file_get_contents($filename);
					$json_obj = json_decode($json);
					if (property_exists($json_obj,"data")) {
						$confdata = $this->get_confsitecode($key);
						$i++;
						$datatable['dt'.$i]['sub_title'] = $confdata['shortname'];
						// 'dt'.$i.'_sub_title';
						// $json_obj->sub_title = $confdata['shortname'];
						if($rec['code'] == $code) {
							$json_obj->sub_title = "";
							$hockey = " Hockey";
							if (preg_match($pattern,$confdata['shortname'])) $hockey = "";
							$page_title = $confdata['shortname']." ".$this->get_full_gender($gender)."'s".$hockey." Standings: ".$full_season;	
						}

						$data['dt'.$i] = $json_obj;
						$newdata = [];

						$orig_team_order = [];
						$ind =0;

						foreach ($json_obj->data AS $ind => $srec) {
							$orig_team_order[$srec[0]] = $ind;
							$ind++;
							$team = $this->team_map($gender,$srec[0]);
							$rnk = "";
							if ($poll_date != "NA") {
								$this->get_team_rank($polldata,$gender,$dm[$division],$srec[0],$poll_date);
							}
							if ($rnk > 0) {$srec[0] = "(".$rnk.") ".$srec[0];}
							//echo $srec[0];
							//var_dump($team);
							$srec[0] = "<a href='/team/".$team->nice."/".strtolower($this->get_full_gender($gender))."s-hockey/'>".$srec[0]."</a>";
							array_push($newdata,$srec);
						}

						if (array_key_exists($rec['code'],$tie_breaker)) {
							$tbdata = [];
							foreach ($tie_breaker[$rec['code']] AS $tname) {
								array_push($tbdata,$newdata[$orig_team_order[$tname]]);
							}
							$json_obj->data = $tbdata;
						} else {
							$json_obj->data = $newdata;
						}
					}
				}
			}
		}
		if ($i > 0) {
			return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title));
		} else {
			return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
		}
	}
}
