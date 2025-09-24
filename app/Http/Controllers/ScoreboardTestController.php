<?php

namespace App\Http\Controllers;
use Response;

class ScoreboardTestController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
		
		$template = "line";
		$gender = "";
		$division = "";
		$code = "";
		$full_season = "";
		$gamedate = "";
		$sitecode= [];
		$refreshTime = 0;

		if ($count > 0) $code = $parts[0];		
		if ($count > 1) $full_season = $parts[1];
		if ($count > 2) $template = $parts[2];
		
		if ($count > 4) {
			$refreshTime = $parts[4];
			$gamedate = $parts[3];
		} else if ($template == "gameday" && $count > 3) {
			$gamedate = $parts[3];
		} else {
			if ($count > 3) {
				$refreshTime = $parts[3];
			}
			date_default_timezone_set('US/Eastern');
	                $gamedate =  date('Y-m-d');
		}
		
		$pattern = "/^division-/";
		
		if (preg_match($pattern,$code)) {
			$p2 = explode("-",$code);
			$ct2 =  count($p2);
			$code = "composite";
			if ($ct2 > 2) {
				$division = strtoupper($p2[1]);
				$gender = $this->get_gender($p2[2]);
			} else {
				$gender = "m";
				$division = "I";
			}
		} else {
			if ($count > 2) {
				$template = "team";
				$gender = $this->get_gender($parts[1]);
				$sitecode = $this->get_sitecode($gender,$code);
				$full_season = $parts[2];
			} elseif ($count > 0) {
				$sitecode = $this->get_confsitecode($code);
				$gender = $sitecode['gender'];
			}
			$division = $sitecode['division'];		
			$code = $sitecode['code'];
		}

		$season = $this->get_season($full_season);
		return array($template,$code,$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime);
	}
	public function get_page_title($type,$code,$gender,$division,$season,$sitecode,$full_season)
	{
		$shortname = "";
		if (array_key_exists("shortname",$sitecode)) {$shortname = $sitecode['shortname']." ";}
		$full_season = substr($season,0,4)."-".substr($season,4,4);
		return $shortname.$this->get_full_gender($gender)."'s Division ".$division." Hockey ".$full_season." Schedule and Results";
	}	
	public function template($template,$code,$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime)
	{
		# parse_uri
		//list ($template,$code,$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime) = $this->parseuri($uri);

		# load conf
		$page_title = $this->get_page_title($template,$code,$gender,$division,$season,$sitecode,$full_season);


		# composite is all in one file
		$datatable = [];
		$filename = $this->get_jsonfilename("scoreboard","composite",$gender,$division,$season,$sitecode);

		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			//$json = str_replace("\n", " ", $jsonfile);
			//echo $json;
			$json_obj = json_decode($json);
			$templatec = str_replace("-","_",$template);
	
			//echo $filename;
	
			if (property_exists($json_obj,"data")) {
				if ($templatec == "scores_template") {
					list ($conf,$result,$last_updated,$status) = $this->$templatec($template,$json_obj,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime);

					return Response::json(array('count' => $conf, 'result' => $result, 'refreshTime' => $last_updated, 'status' =>  $status));
				} else {
					list ($data,$datatable,$page_title,$toDate,$fromDate,$minDate,$maxDate,$last_updated,$status) = $this->$templatec($template,$json_obj,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime);
					return Response::json(array('game_cnt' => count($data['data']), 'json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'toDate' => $toDate, 'fromDate' => $fromDate, 'minDate' => $minDate, 'maxDate' => $maxDate, 'refreshTime' => $last_updated, 'status' =>  $status));
				}
			}
		} 
		return Response::json(array('game_cnt' => count($datatable), 'html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}
	public function line($template,$json,$code,$page_title,$gender,$division,$full_season,$gamedate,$refershTime){
	 	# attempt to load json files
		$ind=0;
		$datatable = [];
		$jdata = [];
		$data = [];

		$season = str_replace( "-", "", $full_season );
		$box = $this->season_box_file($gender,$season);
		$recaps = $this->season_recap_file($gender,$season);
		$polldata = $this->poll_data();
		
		$conf = $this->get_confs($gender,$division);
		$conf['1top-20']['code'] = "t20";
		$conf['1top-20']['shortname'] = "Top 20";	
		ksort($conf);	
		$conf['non-conference']['code'] = "nc";
		$conf['non-conference']['shortname'] = "Non-Conference";
		$conf['exhibition']['code'] = "ex";
		$conf['exhibition']['shortname'] = "Exhibition";
		$jdata['t20'] = [];
		
		$pattern = "/^Hockey/";
		$minDate = "";
		$maxDate = "";
		
		# load keys we will capture
		$key = $this->list_col();
		foreach ($json->data as $rec) {		
			list($game,$minDate,$maxDate,$hrnk,$vrnk) = $this->line_record($rec,$key,$box,$recaps,$polldata,$gender,$division,$minDate,$maxDate);
			if (!(array_key_exists($rec->type,$jdata))) {	$jdata[$rec->type] = [];}
			array_push($jdata[$rec->type],$game);
			if ($rec->type != $rec->hconf) {
				if (!(array_key_exists($rec->hconf,$jdata))) {	$jdata[$rec->hconf] = [];}
				array_push($jdata[$rec->hconf],$game);
			}
			if ($rec->type != $rec->vconf) {
				if (!(array_key_exists($rec->vconf,$jdata))) {	$jdata[$rec->vconf] = [];}
				array_push($jdata[$rec->vconf],$game);
			}
			if ($hrnk OR $vrnk) {
				if (!(array_key_exists($rec->vconf,$jdata))) {	$jdata[$rec->vconf] = [];}
				array_push($jdata['t20'],$game);
			}
		}
		
		# loop through data 
		foreach ($conf as $key => $rec) {			
			if ($code == "composite" or $rec['code'] == $code) {
				if (array_key_exists($rec['code'],$jdata)) {						
					$ind++;
					$datatable['dt'.$ind]['sub_title'] = 'dt'.$ind.'_sub_title';
					$jdata['sub_title'] = $conf[$key]['shortname'];
					
					if($rec['code'] == $code) {
						$jdata['sub_title'] = "";
						$hockey = " Hockey";
						if (preg_match($pattern,$conf[$key]['shortname'])) $hockey = "";
						$page_title = $conf[$key]['shortname']." ".$this->get_full_gender($gender)."'s".$hockey." Standings: ".$full_season;	
					}
					$data['dt'.$ind]['data'] = $jdata[$rec['code']];
					$data['dt'.$ind]['sub_title'] = $jdata['sub_title'];
					$data['dt'.$ind]['meta_data'] = $json->meta_data;
				}
			}
		}
		$toDate = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")+4, date("Y")));
		$fromDate = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-4, date("Y")));
		if ($fromDate > $maxDate ) {
			$fromDate = $minDate;
			$toDate = date("Y-m-d", mktime(0, 0, 0, date("m",strtotime($fromDate))  , date("d",strtotime($fromDate))+8, date("Y",strtotime($fromDate))));
		}
		$data['meta_data'] = $json->meta_data;
		
		return array($data,$datatable,$page_title,$toDate,$fromDate,$minDate,$maxDate,0,"final");
	}

	public function scores($division,$full_gender,$full_season,$gamedate,$refreshTime) {
                $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season($full_season);
                $sitecode= [];

                return $this->template("scores_template","composite",$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime);
        }

	public function scores_template($template,$json,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime){

		$conf = [];
		$result = [];
		$last_updated = "";
		$gdate = str_replace("-", "", $gamedate);

		$scroll_status = "final";
		list($livejson,$last_updated) = $this->livescoredata($gender,$division,$gdate);

		if ($livejson != null) {

			$conflist = $this->get_confs($gender,$division);
			foreach ($conflist AS $confnice => $cnf) {
				$conf[$cnf['code']] = 0;
			}

			$res_map = [
				"visitor" => "visitor",
				"home" => "home",
				"vscore" => "vscore",
				"hscore" => "hscore",
				"ots" => "ots",
				"type" => "type",
				"game_id" => "gameid",
				"starttime" => "starttime",
				"arena_name" => "name"
			];

			$pdmap = ["1" => "st","2" => "nd","3" => "rd","4" => "th","5" => "th","6" => "th","7" => "th","8" => "th"];
			$ind = 0;

			foreach ($json->data as $rec) {
				if ($rec->gdate != $gdate) {continue;}

				if(!array_key_exists($rec->type,$conf)) {
					$conf[$rec->type] = 0;	
				}
				$conf[$rec->type]++;

                       	 	$game = [];
				foreach ($res_map as $key => $value) {
					$game[$value] = $rec->$key;	
				}
				$game_id = $rec->game_id;
				$game['last_modified'] = 0;
				$game['ind'] = $ind;
				$ind++;

				if (property_exists($livejson,$game_id)) {
					$live = $livejson->$game_id;
					
					$minutesot = 5;
					if (property_exists($live,"minutesot")) {$minutesot = $live->minutesot;}

					$game['last_modified'] = $live->last_modified;
					$game['ls'] = 0;
					$game['last_play'] = "";
					$game['location'] = "";
					$game['pd_suffix'] = "";
					$game['pd'] = "";
					$game['pd_time'] = $rec->starttime;
					if ($game['vscore'] != "") {
						$ext = "";
						if($game['ots'] ==  1) {
							$ext = " OT";
						} elseif ($game['ots'] > 1) {
							$ext = " ".$game['ots']."OT";
						}
						$game['pd_time'] = "F".$ext;
					}					
	
					$game['vshots'] = 0;
                                	$game['hshots'] = 0;
                                	if (property_exists($live,"vshots")) $game['vshots'] = $live->vshots;
                                	if (property_exists($live,"hshots")) $game['hshots'] = $live->hshots;
	
					if ($live->complete == "N" && $game['vscore'] == "") {
						$countdown = 0;
						$teamsflip = 0;
						if (property_exists($live,"countdown")) {$countdown = $live->countdown;}
						if (property_exists($live,"teamsflip")) {$teamsflip = $live->teamsflip;}
       			                        if ($teamsflip) {
                                        		$hscore = $live->hscore;
                                        		$live->hscore = $live->vscore;
                                        		$live->vscore = $hscore;
                                		}
	
						$scroll_status = "live";
						$game['ls'] = 1;
						$game['lhscore'] = $live->hscore;
						$game['lvscore'] = $live->vscore;
						$game['pd'] = $live->period;
						$game['pd_suffix'] = $pdmap[$game['pd']];
						$game['pd_time'] = $this->pd_countdown_time($game['pd'],$live->clock,$minutesot,$countdown);
					} else {
						$game['pd'] = "F";
					}
				}	
				array_push($result,$game);
			}

			usort($result,function ($item1, $item2) {
                                return strcmp($item2['last_modified'].$item2['ind'],$item1['last_modified'].$item1['ind']);
			});

			if ($refreshTime != 0 && $refreshTime == $last_updated) {
                                return array([],[],$last_updated,$scroll_status);
                        }
		}

		return array($conf,$result,$last_updated,$scroll_status);
	}

	public function gameday($division,$full_gender,$full_season) {
                $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season($full_season);
		$sitecode= [];
		$gamedate =  date('Y-m-d');
		$refreshTime = 0;

                return $this->template("gameday_template","composite",$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime);
        }

	public function gameday_date($division,$full_gender,$full_season,$gamedate,$refreshTime) {
		$gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
		$season = $this->get_season($full_season);
		$sitecode= [];

		return $this->template("gameday_template","composite",$gender,$division,$season,$sitecode,$full_season,$gamedate,$refreshTime);
	}

	public function gameday_template($template,$json,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime){
		$datatable = [];
		$data = [];
		$data['data'] = [];
		$jdata = [];

		$gmdate = str_replace("-", "", $gamedate);

		$scroll_status = "final";
		list($livejson,$last_updated) = $this->livescoredata($gender,$division,$gmdate);

		$season = str_replace( "-", "", $full_season );
		$box = $this->season_box_file($gender,$season);
		$recaps = $this->season_recap_file($gender,$season);
		$polldata = $this->poll_data();
		
		$sub = 0;
		if (!$gamedate) {
			$sub = date("H") > 10 ? 0 : 1;
			$gamedate = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - $sub, date("Y")));
		}

		$minDate = $gamedate;
		$maxDate = "";

		$pdmap = ["0" => "", "1" => "st","2" => "nd","3" => "rd","4" => "th","5" => "th","6" => "th","7" => "th","8" => "th"];

	        # load keys we will capture
		$key = $this->list_col();

		$ind = 0;
		$trec = (array)$json->trec;
		foreach ($json->data as $rec) {
			$jdata[$rec->sort_date] = 1;
			if ($minDate > $rec->sort_date) {$minDate = $rec->sort_date;}
			if ($maxDate < $rec->sort_date) {$maxDate = $rec->sort_date;}
 
			if ($rec->gdate != $gmdate) {continue;}

			$game = (array)$rec;
			$gdate = $game['gdate'];
                        $game['last_modified'] = 0;
                        $game['ind'] = $ind;
			$ind++;

			if (array_key_exists($game['home'],$trec) ) {
				if (property_exists($trec[$game['home']],$gdate)) {
					$game['hrec'] = $trec[$game['home']]->$gdate;
				} else {
					$game['hrec'] = $trec[$game['home']]->current;
				}
			} 
			if (array_key_exists($game['visitor'],$trec) ) {
				if (property_exists($trec[$game['visitor']],$gdate)) {
                                        $game['hrec'] = $trec[$game['visitor']]->$gdate;
                                } else {
                                        $game['vrec'] = $trec[$game['visitor']]->current;
                                }
			}
			$hrnk = $this->get_team_rank($polldata,$gender,$division,$rec->home_name,$rec->sort_date);
			if ($hrnk) {$game['home_name'] = "<span class='pollrank'>".$hrnk."</span> ".$game['home_name'];}
			$vrnk = $this->get_team_rank($polldata,$gender,$division,$rec->vis_name,$rec->sort_date);
			if ($vrnk) {$game['vis_name'] = "<span class='pollrank'>".$vrnk."</span> ".$game['vis_name'];}

			if ($minDate == "" or $minDate > $rec->sort_date) {$minDate = $rec->sort_date;}
			if ($maxDate == "" or $maxDate < $rec->sort_date) {$maxDate = $rec->sort_date;}
			if (array_key_exists($rec->game_id,$recaps) ) {
				$game['recap'] = "<a href=".$recaps[$rec->game_id]['link'].">Recap</a>";
			}

			$game_complete = "N";
                        if ($rec->vscore != "") {
                        	$game_complete = "Y";
                        }

			$hometeam = $this->team_map($gender,$game['home']);
                        $visteam = $this->team_map($gender,$game['visitor']);

                        if ($hometeam != null) {
                                $game['home_chs'] = strtoupper($hometeam->chs_team_code);
                        } else {
                                $game['home_chs'] = substr(strtoupper($game['home']),0,3);
                        }
                        if ($visteam != null) {
                                $game['vis_chs'] =  strtoupper($visteam->chs_team_code);
                        } else {
                                $game['vis_chs'] = substr(strtoupper($game['visitor']),0,3);
                        }

			$game_id = $rec->game_id;
			if (!array_key_exists("pd_time",$game)) {$game['pd_time'] = "";}
			if (!array_key_exists("pd_suffix",$game)) {$game['pd_suffix'] = "";}

                        if ($game['vscore'] != "") {
                                $ext = "";
                                if($game['ots'] ==  1) {
                                	$ext = " OT";
                                } elseif ($game['ots'] > 1) {
                                       	$ext = " ".$game['ots']."OT";
                                }
                                $game['pd'] = "F";
                        	$game['pd_time'] = "F".$ext;
                        }

                        if (property_exists($livejson,$game_id)) {
                                $live = $livejson->$game_id;

				$minutesot = 5;
				$countdown = 0;
				$teamsflip = 0;

				$game['last_modified'] = $live->last_modified;

                                if (property_exists($live,"minutesot")) {$minutesot = $live->minutesot;}
				if (property_exists($live,"countdown")) {$countdown = $live->countdown;}
				if (property_exists($live,"teamsflip")) {$teamsflip = $live->teamsflip;}
				if ($teamsflip) {
					$hscore = $live->hscore;
					$live->hscore = $live->vscore;
					$live->vscore = $hscore;	
				}

                                if ($live->complete == "N" && $game['vscore'] == "") {
                                        $scroll_status = "live";
                                        $game['lhscore'] = $live->hscore;
                                        $game['lvscore'] = $live->vscore;
                                        $game['pd'] = $live->period;
                                        $game['pd_suffix'] = $pdmap[$game['pd']];
                                        $game['pd_time'] = $this->pd_countdown_time($game['pd'],$live->clock,$minutesot,$countdown);
					$game['starttime'] = "";
                                } else {
                                        $game['pd'] = "F";
                        	}
                        }
			array_push($data['data'],$game);
		}
		
		$data['dates'] = array_keys($jdata);
		
		if ($maxDate < $gamedate) {
			#$gamedate = $maxDate;
		} else {
			$gdateMin = $minDate;
			$gdateMax = $maxDate;
			$prev = $minDate;
                        $next = $maxDate;

			foreach ($data['dates'] as $gd) {
				if ($gdateMin < $gd AND $gd <= $gamedate) {
					$gdateMin = $gd;
				}
				if ($gdateMax > $gd AND $gd >= $gamedate) {
					$gdateMax = $gd;
				}
				if ($prev < $gd AND $gd < $gamedate) {
                                        $prev = $gd;
                                }
                                if ($next > $gd AND $gd > $gamedate) {
                                        $next = $gd;
                                }
			}

			#$gamedate = $sub ? $gdateMin : $gdateMax;
			$data['prev'] = $prev;
			$data['next'] = $next;
		}

		if (count($data['data'])) {
                        usort($data['data'], function ($item1, $item2) {
                                return strcmp($item1['gconf_name'].$item1['last_modified'].$item1['starttime'],$item2['gconf_name'].$item2['last_modified'].$item2['starttime']);
                        });
		}

		if ($refreshTime != 0 && $refreshTime == $last_updated) {
                	return array(['data' => []],[],"","","","","",$last_updated,$scroll_status);
                }
		
		return array($data,$datatable,$page_title." - Game Day Schedule",$maxDate,$minDate,$minDate,$maxDate,$last_updated,$scroll_status);
	}

	function game_clock($clock,$period,$minutesot) {
                list($min,$sec) = explode(":",$clock);

		$pertime = 20;
                if ($period>3) {$pertime = $minutesot;}

                $game_clock = ($pertime-1-$min).":".substr(160-$sec,1,2);

                if ($sec == 0) {
                         $game_clock = ($pertime-$min).":00";
                }

                return $game_clock;
        }


	function livescoredata($gender,$division,$gdate) {

                $livescorefile = config('services.JSON_DIR').$gender."/box/".$gdate."/livescores.json";

                $livescoredata = (object)[];
                $lsRefreshTime = 0;
                if (file_exists($livescorefile)) {
                        $stat = stat($livescorefile);
                        $lsRefreshTime = $stat['mtime'];
                        $file = file_get_contents($livescorefile);
                        $livescoredata = json_decode($file);
                }

                return array($livescoredata,$lsRefreshTime);
        }


	public function team($template,$json,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime){
		$datatable = [];
		$data = [];
		$jdata = [];
		$minDate = "";
		$maxDate = "";

		$season = str_replace( "-", "", $full_season );
		$box = $this->season_box_file($gender,$season);
		$recaps = $this->season_recap_file($gender,$season);
		$polldata = $this->poll_data();

	    	# load keys we will capture
		$key = $this->list_col();
		foreach ($json->data as $rec) {
			if ($rec->home != $code && $rec->visitor != $code) {continue;}
			list($game,$minDate,$maxDate,$hrnk,$vrnk) = $this->line_record($rec,$key,$box,$recaps,$polldata,$gender,$division,$minDate,$maxDate);
			array_push($jdata,$game);
		}
		$datatable['sub_title'] = 'dt1_sub_title';
		$data['data'] = $jdata;
		$data['meta_data'] = $json->meta_data;
		$data['sub_title'] = "";
		return array($data,$datatable,$page_title,$maxDate,$minDate,$minDate,$maxDate,0,"final");
	}
	public function composite_schedule($template,$json,$code,$page_title,$gender,$division,$full_season,$gamedate,$refreshTime){
		$datatable = [];
		$data = [];
		$jdata = [];
		$minDate = "";
		$maxDate = "";

		$season = str_replace( "-", "", $full_season );
		$box = $this->season_box_file($gender,$season);
		$recaps = $this->season_recap_file($gender,$season);
		$polldata = $this->poll_data();

	    # load keys we will capture
		$key = $this->list_col();
		foreach ($json->data as $rec) {
			list($game,$minDate,$maxDate,$hrnk,$vrnk) = $this->line_record($rec,$key,$box,$recaps,$polldata,$gender,$division,$minDate,$maxDate);
			array_push($jdata,$game);
		}
		$datatable['dt1']['sub_title'] = 'dt1_sub_title';
		$data['dt1']['data'] = $jdata;
		$data['dt1']['meta_data'] = $json->meta_data;
		$data['dt1']['sub_title'] = "";
		return array($data,$datatable,$page_title." - Composite Schedule",$maxDate,$minDate,$minDate,$maxDate,0,"final");
	}
	public function line_record($rec,$key,$box,$recaps,$polldata,$gender,$division,$minDate,$maxDate){
		
			$game = [];
			foreach ($key as $k) {
				array_push($game,$rec->$k);
			}

			$hrnk = $this->get_team_rank($polldata,$gender,$division,$rec->home_name,$rec->sort_date);
			if ($hrnk) {$game[3] = "<span class='pollrank'>".$hrnk."</span> ".$game[3];}
			$vrnk = $this->get_team_rank($polldata,$gender,$division,$rec->vis_name,$rec->sort_date);
			if ($vrnk) {$game[1] = "<span class='pollrank'>".$vrnk."</span> ".$game[1];}

			if ($minDate == "" or $minDate > $rec->sort_date) {$minDate = $rec->sort_date;}
			if ($maxDate == "" or $maxDate < $rec->sort_date) {$maxDate = $rec->sort_date;}
			$gameday = "";
			$gametracker = "";
			if (array_key_exists($rec->game_id,$box) ) {
				$gameday .= "<a href=".$box[$rec->game_id]['link'].">Box</a>";
			}
			if (array_key_exists($rec->game_id,$recaps) ) {
				$gameday .= "<a href=".$recaps[$rec->game_id]['link'].">Recap</a>";
			}
			if (!$gameday) {
				$gameday = $rec->game_day;
				$gametracker = $rec->gametracker;
                if ($period>3) {$pertime = 5;}
			}
			array_push($game,$gameday);
			array_push($game,$gametracker);
			array_push($game,strtoupper($rec->note));

			return array($game,$minDate,$maxDate,$hrnk,$vrnk);
	}

	public function list_col(){
		return array("sort_date","vis_name","vscore","home_name","hscore","ot","game_type");
	}

	function pd_countdown_time($per,$per_time,$minsot,$countdown) {

		if ($countdown) {return $per_time;}

	        $tsplit = explode(":",$per_time);
        	$sup = 60 * $tsplit[0] + $tsplit[1];

	        if ($per<4) {
        	        $pmins = 1200;
        	} else {
                	$pmins = $minsot*60;
        	}

	        $sdown = $pmins-$sup;
        	$mins = floor ($sdown / 60);
        	if ($mins == 0) $mins = "";

	        $secs = $sdown % 60;
        	if (strlen($secs)==1) $secs = 0 . $secs;

	        return $mins.":".$secs;
	}
}
