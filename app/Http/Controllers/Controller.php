<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

date_default_timezone_set('America/Chicago');
$inc_dir = config('services.INC_DIR');
set_include_path($inc_dir);
include_once("classes/timedata_app");

abstract class Controller extends BaseController
{
    	use DispatchesJobs, ValidatesRequests;

	public function parseuri($parts)
	{
		// $parts = explode("/",$uri);
		$count =  count($parts);
		
		$template = "";
		$code = "";
		$gender = "";
		$full_season = "";
		
		if ($count > 0) $template = $parts[0];
		if ($count > 2) $gender = $this->get_gender($parts[2]);
		if ($count > 1) $sitecode = $this->get_sitecode($gender,$parts[1]);
		if ($count > 3) $full_season = $parts[3];

		$season = $this->get_season($full_season);
	
		return array($template,$sitecode['code'],$gender,$sitecode['division'],$season,$sitecode,$full_season);
	}
	
	# eg /home/json/m/I/roster/20142015/umn.json
	public function get_jsonfilename($template,$code,$gender,$division,$season)
	{	
		return config('services.JSON_DIR').$gender."/".$division."/".$template."/".$season."/".$code.".json";
	}

	public function get_gender($full_gender)
	{	
		$gm['mens-hockey'] = "m";
		$gm['womens-hockey'] = "w";
		$gm['men'] = "m";
		$gm['women'] = "w";
		$gm['mens'] = "m";
		$gm['womens'] = "w";
				
		return array_key_exists($full_gender,$gm) ? $gm[$full_gender] : $full_gender;
	}

	public function get_full_gender($gender)
	{	
		$gm['m'] = "Men";
		$gm['w'] = "Women";

		return array_key_exists($gender,$gm) ? $gm[$gender] : $gender;
	}

	public function get_sitecode($gender,$sitecode)
	{	
		$data = file_get_contents(config('services.JSON_DIR')."/site/team.json");
		$json = json_decode($data,true);
		return array_key_exists($sitecode,$json[$gender]) ? $json[$gender][$sitecode] : ['code' => $sitecode, 'division' => "I"];
	}

	public function get_conf_team($gender,$division,$conf_code)
        {
                $data = file_get_contents(config('services.JSON_DIR')."/site/team.json");
                $json = json_decode($data,true);

		$team = array();
		foreach ($json[$gender] AS $team_nice=> $tm) {
			if($tm['conf_code'] == $conf_code) {
				array_push($team,$tm);
			}
                }

		return $team;
        }

        public function team_map($gender,$code)
	{
		$tmd = $this->load_team_map_data();
		if (array_key_exists($code,$tmd[$gender])) {return $tmd[$gender][$code];}
                return null;
        }

	public function load_teamall_map_data()
	{
                if(!(property_exists($this,"team_map_data"))) {
                        $data = file_get_contents(config('services.JSON_DIR')."/site/teamall.json");
                        $json = json_decode($data);
                        $team_map_data = [];
                        foreach ($json AS $gender => $teams) {
                                foreach ($teams AS $team_nice=> $tm) {
                                        $team_map_data[$gender][$tm->code] = $tm;
					$team_map_data[$gender][$tm->shortname] = $tm;
					//if (!array_key_exists($tm->chs_team,$team_map_data[$gender])) {
						$team_map_data[$gender][$tm->chs_team] = $tm;
					//}
                                }
                        }
                        $this->team_map_data = $team_map_data;
                }

                return $this->team_map_data;
        }

        public function load_team_map_data()
	{
                if(!(property_exists($this,"team_map_data"))) {
                        $data = file_get_contents(config('services.JSON_DIR')."/site/team.json");
                        $json = json_decode($data);
                        $team_map_data = [];
                        foreach ($json AS $gender => $teams) {
                                foreach ($teams AS $team_nice=> $tm) {
                                        $team_map_data[$gender][$tm->code] = $tm;
					$team_map_data[$gender][$tm->shortname] = $tm;
					//if (!array_key_exists($tm->chs_team,$team_map_data[$gender])) {
                                                $team_map_data[$gender][$tm->chs_team] = $tm;
                                        //}
                                }
                        }
                        $this->team_map_data = $team_map_data;
                }

                return $this->team_map_data;
        }

        public function load_conf_map_data()
        {
                if(!(property_exists($this,"conf_map_data"))) {
                        $data = file_get_contents(config('services.JSON_DIR')."/site/conf.json");
                        $json = json_decode($data);
                        $conf_map_data = [];
                        foreach ($json AS $conf_nice => $rec) {
                        	$conf_map_data[$rec->code] = $rec;
                        	$conf_map_data[$conf_nice] = $rec;
				$conf_map_data[$rec->shortname] = $rec;
                        }
                        $this->conf_map_data = $conf_map_data;
                }

                return $this->conf_map_data;
        }

        public function conf_map($code)
        {
                $conf_map_data = $this->load_conf_map_data();
                return $conf_map_data[$code];
        }

	public function get_confs($gender,$division)
	{	
		$data = file_get_contents(config('services.JSON_DIR')."/site/conf.json");
		$json = json_decode($data,true);
		$conf = [];
		
		foreach ($json as $key => $rec) {
			if ($rec['division'] == $division and $rec['gender'] == $gender) {
				$conf[$key] = $rec;
			}
		}

		return $conf;
	}	

	public function get_confsitecode($sitecode)
	{	
		$data = file_get_contents(config('services.JSON_DIR')."/site/conf.json");
		$json = json_decode($data,true);

		return array_key_exists($sitecode,$json) ? $json[$sitecode] : ['code' => $sitecode, 'division' => "I"];
	}	
	
	public function get_season($full_season)
	{	
		if ($full_season <> "" && $full_season <> "current") {
			$season = preg_replace( "/-/", "", $full_season );
		} else {
			# if empty default to current
			$season = $this->get_current_season();
		}
		
		return $season;
	}
	
	public function get_current_season()
	{	
		
		$full_season = $this->get_current_full_season();
		$season = preg_replace( "/-/", "", $full_season );
	
		return $season;
	}	

	public function get_current_full_season()
	{	
		
		$td=new \timedata();
		$season = $td->season;
		
		return substr($season,0,4)."-".substr($season,4,4);
	}	
		
	public function get_team_rank($json,$gender,$division,$team,$date)
	{
		if (!$json) {
			$json = $this->poll_data();
		}
		
		if (property_exists($json->poll_data->$gender->$division,$date)) {
			$polldate = $json->poll_data->$gender->$division->$date->PollDate;
		} elseif ($date >= $json->poll_data->$gender->$division->first_poll_date->PollDate) {
			$polldate = $json->poll_data->$gender->$division->current->PollDate;
		} else {
			$polldate = "NA";
		}
		$rnk = "";
		
		if(property_exists($json->data->$gender->$division,$polldate)) {
			$data = $json->data->$gender->$division->$polldate;

			foreach ($data as $key => $pd) {
				if ($key == "data") {
					foreach ($pd as $rec) {
						if ($rec->shortname == $team) $rnk = $rec->rnk;
					}
				}
			}
		}

		return $rnk;
	}
	
	public function poll_data() 
	{
		$json = file_get_contents(config('services.JSON_DIR')."/site/poll.json");
		$json_data = json_decode($json);
		return $json_data;
	}

	public function static_path() 
	{
		return config('services.JSON_DIR');
	}
	
	public function season_box_file($gender,$season) 
	{
		$filename = $this->static_path().$gender."/box/gd".$season.".json";
		$json_data = [];
		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			$json_data = json_decode($json,true);
		}
		return $json_data;
	}
	
    	public function season_recap_file ($gender,$season) 
    	{
		$filename = $this->static_path().$gender."/recap/gd".$season.".json";
		$json_data = [];
		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			$json_data = json_decode($json,true);
		}
		return $json_data;

	}

        public function SortByKeyValue($data, $sortKey, $sort_desc) {
                if (empty($data) or empty($sortKey)) return $data;

                $ordered = array();
                foreach ($data as $key => $value)
                        $ordered[$value[$sortKey]][$key] = $value;

                if($sort_desc == 1) {
                        krsort($ordered);
                } else {
                        ksort($ordered);
                }

                return array_values($ordered); // array_values() added for identical result with multisort*
        }

    	public function _init_db ()
    	{
		$con = mysqli_connect("private-db-mysql-nyc3-80737-do-user-9001989-0.b.db.ondigitalocean.com","app","ofnfajp9fxmhcqra","uschocontent",25060);
                return $con;
        }

}
