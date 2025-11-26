<?php

namespace App\Http\Controllers;
use Response;

class AppTeamInfo extends AppController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "unknown";
		$gender = "m";
		$division = "I";
		$conf_code = "";
		$team_code = "";

		if ($count > 0) {$template = $parts[0];}
		
		if ($count > 1) {

			$gender = "";
			$pattern = "/^division-/";
	
			$divgen = explode("-",$parts[1]);
			$count2 =  count($divgen);
			if ($count2 > 0) {
				$division = $divgen[1];
				$gender = $this->get_gender($divgen[2]);;
			}
		} 
		
		if ($count > 2) $conf_code = $parts[2];
		if ($count > 3) $team_code = $parts[3];

		return array($gender,strtoupper($division),$conf_code, $team_code, $template);
	}

	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($gender,$division,$conf_code,$team_code,$template) = $this->parseuri($uri);
		if (method_exists($this,$template)) {
			return $this->$template($gender,$division,$conf_code,$team_code);
		} else {
			return Response::json(array('success' => 0, 'message' => 'Request Denied'));
		}
	}

	public function nav_list_wlogo($lastModified=0) {

		$res = $this->nav_list_data();
		$res['logo_list'] = $this->test_logo_change($lastModified);

		return Response::json($res);
	}

	public function nav_list() {
		$res = $this->nav_list_data();	
		return Response::json($res);
	}

	public function nav_list_data() {
		$data = ['divGen' => [
				['name' => "Men's DI", 'nice' => "d-i-men", 'division' => 'I', 'gender' => 'm'],
				['name' => "Women's DI", 'nice' => "d-i-women", 'division' => 'I', 'gender' => 'w'],
				['name' => "Men's DII/III", 'nice' => "d-iii-men", 'division' => 'III', 'gender' => 'm'],
				['name' => "Women's DIII", 'nice' => "d-iii-women", 'division' => 'III', 'gender' => 'w']
			], 'confList' => [], 'teamList' => []];
		
		foreach ($data['divGen'] AS $dg) {
			list ($confarray) =  $this->conf_list_data($dg['gender'],$dg['division']);
			$data['confList'][$dg['nice']] = $confarray;
			foreach ($confarray AS $cf) {
				list ($team) =  $this->team_list_data($dg['gender'],$dg['division'],$cf['code']);
				$data['teamList'][$dg['nice']][$cf['code']] = $team;
			}
		}	

		return array('success' => 1, 'data' => $data, 'ios' => '3.20', 'android' => '2.33');
	}

	private function test_logo_change($lastModified) {
		return [
			['url' => 'https://static.uscho.com/img/images/logos/uaa.gif/target.webp', 'file' => 'uaa.webp']
		];
	}

	public function conf_list ($gender,$division,$conf,$team_code) {

		list ($confarray) =  $this->conf_list_data($gender,$division);
		return Response::json(array('data' => $confarray));
	}

        public function conf_list_data ($gender,$division) {

                $confarray = array();
                $independent = array();

                list($confarray,$independent) = $this->active_conf($gender,$division,$confarray,$independent);

                if ($division == "III") {
                        list($confarray,$independent) = $this->active_conf($gender,"II",$confarray,$independent);
                }

                usort($confarray, function ($item1, $item2) {
                        return strcmp($item1['shortname'], $item2['shortname']);
                });

                usort($independent, function ($item1, $item2) {
                        return strcmp($item1['shortname'], $item2['shortname']);
                });

                foreach($independent AS $rec) {
                        array_push($confarray,$rec);
                }

                return array($confarray);
        }

	public function active_conf ($gender,$division,$confarray,$independent) {
		$conf = $this->get_confs($gender,$division);
                $cur_season = $this->get_current_season();

                foreach($conf AS $nice => $rec) {
                        if ($rec['end_year'] >= $cur_season) {
                                if($rec['code'] == 'i1' || $rec['code'] == 'i3' || $rec['code'] == 'i2' ) {
                                        array_push($independent,$rec);
                                } else {
                                        array_push($confarray,$rec);
                                }
                        }
                }

		return array($confarray,$independent);
	}

	public function team_list ($gender,$division,$conf_code,$team_code) {
		list($team) = $this->team_list_data($gender,$division,$conf_code);
		return Response::json(array('data' => $team));
        }

	public function team_list_data ($gender,$division,$conf_code) {
                $team = $this->get_conf_team($gender,$division,$conf_code);

                if (count($team) == 0 && $division == "III") {$team = $this->get_conf_team($gender,"II",$conf_code);}

                usort($team, function ($item1, $item2) {
                        return strcmp($item1['shortname'], $item2['shortname']);
                });

		return array($team);
        }

	public function roster ($division,$full_gender,$conf_code,$team_code,$refreshTime,$req) {

		$division = strtoupper($division);
		$gender = $this->get_gender($full_gender);
		$full_season = $this->get_current_full_season();
                $season = $this->get_season($full_season);

                # kludge for Northeast 10 n10
                if ($conf_code == "n10") {$division = "II";}

		$filename = $this->get_jsonfilename("roster",$team_code,$gender,$division,$season,$team_code);

		$page_title = $full_season. " Roster";
                $columns = array(0,1,2,3,4,5,6);
                $weights = ["1.0f","3.0f","1.0f","1.0f","1.0f","1.0f","2.0f","2.0f"];

		list($data,$header,$stat) = $this->app_data($filename,$columns,-1,[]);

		usort($data, function ($item1, $item2) {
                        return $item1[0] <=> $item2[0];
                });

		$tm = $this->team_map($gender,$team_code);
		$info = $this->current_team_info($gender,$division,$season,$conf_code,$tm->shortname);
	
		return $this->response_data($page_title, $columns, $weights,[],"",$data,$header,$stat,$info,[],[],"roster");
	}

	public function current_team_info($gender,$division,$season,$conf_code,$team_name){
                $json = [];

          
                $npifilename = $this->get_jsonfilename("ranking","npiraw",$gender,$division,$season,"sc");
		$standfilename = $this->get_jsonfilename("standings",$conf_code,$gender,$division,$season,"");

                # poll is all in one file
		$poll_div = $division; 
		if ($conf_code == "n10") {$poll_div = "III";}
                $poll_json_obj = $this->poll_data();
                $polldate = $poll_json_obj->poll_data->$gender->$poll_div->current->PollDate;
                $polldata = $poll_json_obj->data->$gender->$poll_div->$polldate;

                $json['poll'] = [];
                $poll = [];

                foreach ($polldata->data as $rec) {
                	$poll[$rec->shortname] = $rec->rnk;
                }

                $json['poll'] = "";
		if (array_key_exists($team_name,$poll)) {$json['poll'] = $poll[$team_name];}

		$json['record'] = "";
		$json['homerecord'] = "";
		$json['roadrecord'] = "";
		$json['pwr'] = "";
		$json['rpi'] = "";

                if (file_exists($npifilename)) {
                	$jsont = file_get_contents($npifilename);
                        $json_obj = json_decode($jsont);

			if (property_exists($json_obj,$team_name)) {
                        	if (property_exists($json_obj->$team_name,"record") && property_exists($json_obj->$team_name,"winpctrnk")) {
					$json['record'] = "Rec: ".$json_obj->$team_name->record." (".$json_obj->$team_name->winpctrnk.")";
				} else {
					$json['record'] = "Rec: NA";
				}
			}
                }

/*
               if (file_exists($rpifilename)) {
                 	$jsont = file_get_contents($rpifilename);
                        $json_obj = json_decode($jsont);

			if (property_exists($json_obj,$team_name)) {
				$json['homerecord'] = "Home: ".$json_obj->$team_name->home->rec;
				$json['roadrecord'] = "Road: ".$json_obj->$team_name->road->rec;
			}
                }
*/

		if (file_exists($standfilename)) {
                        $jsont = file_get_contents($standfilename);
                        $standjson = json_decode($jsont);
			$stand = [];

			$ind = 1;
			$rnk = 1;
			$cpts = 0;
			foreach ($standjson->data as $rec) {
				if ($rec[3] != $cpts) {
					$rnk = $ind;
					$cpts = $rec[3];
				}
				$res['crecord'] = $rec[2];
				$res['record'] = $rec[6];
				$res['rnk'] = $rnk;
                        	$stand[$rec[0]] = $res;
				$ind++;
			}

                	if (array_key_exists($team_name,$stand)) {
                		$json['confrecord'] = "Cnf: ".$stand[$team_name]['crecord']." (".$stand[$team_name]['rnk'].")";
                		$json['record'] = "Rec: ".$stand[$team_name]['record'];
                	}
                }

                return $json;
	}
}
