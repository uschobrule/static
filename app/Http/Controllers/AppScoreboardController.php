<?php

namespace App\Http\Controllers;
use Response;

class AppScoreboardController extends AppTeamInfo
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "conf_list";
		$gender = "m";
		$division = "I";
		$conf_code = "";
		$filter1 = "";
		$filter2 = "";
		$refreshTime = 0;
		$scrollRefreshTime = 0;
		
		if ($count > 1) {
			$template = $parts[0];

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
		if ($count > 3) $filter1= $parts[3];
		if ($count > 4) $filter2 = $parts[4];
		if ($count > 6) {
			$refreshTime = $parts[5];
			$scrollRefreshTime = $parts[6];
		}
		$boxMode = "all";
		if ($count > 7) {
                        $boxMode = $parts[7];
                }
		return array($gender,strtoupper($division),$conf_code,$filter1,$filter2,$template,$refreshTime,$scrollRefreshTime,$boxMode);
	}

	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($gender,$division,$conf_code,$filter1,$filter2,$template,$refreshTime,$scrollRefreshTime,$boxMode) = $this->parseuri($uri);
		if (method_exists($this,$template)) {
			return $this->$template($gender,$division,$conf_code,$filter1,$filter2,$refreshTime,$scrollRefreshTime,$boxMode);
		} else {
			return Response::json(array('success' => 0, 'message' => "Currently not available", 'data' => [], 'header'=> [], 'weights' => [], 'info' => [], 'page_title' => "", 'footer' => "", 'prehead' => []));
		}
	}

        public function schedule($division,$full_gender,$conf_code,$team_code,$refreshTime,$scrollRefreshTime) {

		$gender = $this->get_gender($full_gender);
		$division = strtoupper($division);

		$full_season = $this->get_current_full_season();
                $season = $this->get_season($full_season);

	 	if ($conf_code == "n10") {$division = "II";}

		$filename = $this->get_jsonfilename("scoreboard","composite",$gender,$division,$season);

		$page_title = $full_season." Schedule and Results";
		
                $box = $this->season_box_file($gender,$season);
                $recaps = $this->season_recap_file($gender,$season);
		$polldata = $this->poll_data();

		date_default_timezone_set('US/Eastern');
                $cur_gdate = date('Ymd');// "20190411";
		list($livescoredata,$lsRefreshTime) = $this->livescoredata($gender,$division,$cur_gdate);

                if (file_exists($filename)) {
                        $file = file_get_contents($filename);
			$stat = stat($filename);

			$json = json_decode($file);
			$data = [];
			$header = ["Score","Code","Home","Opp Code","Visitor","Game Info","Arena","Note","TV","gameID","Res"];
			$weights = [];
			$team_name = "";
			$status = "final";

                	foreach ($json->data as $rec) {
                        	if ($rec->home != $team_code && $rec->visitor != $team_code) {continue;}
				if ($team_name == "" && $rec->home == $team_code) {
					$team_name = $rec->home_name;
				} else if ($team_name == "" && $rec->visitor == $team_code) {
					$team_name = $rec->vis_name;
				}

				$game_id = $rec->game_id;
				$game_complete = "N";
				if ($rec->vscore != "") {
					$game_complete = "Y";
				}
				list($last_modified,$game_complete,$status) = $this->livescore_replace($livescoredata,$game_id,$rec,$status,$game_complete);
				list ($hteam,$vteam,$gm) = $this->org_game($rec,$team_code,$json->trec,$polldata,$gender,$division,$game_complete);
			
				$game = [];
	
				foreach ($hteam AS $ind => $rec) {
					array_push($game,$hteam[$ind]);
				}
				foreach ($vteam AS $ind => $rec) {
                                        array_push($game,$vteam[$ind]);
                                }
				foreach ($gm AS $ind => $rec) {
                                        array_push($game,$gm[$ind]);
                                }

				array_push($game,$last_modified);

                        	array_push($data,$game);

				if ($last_modified > $stat['mtime']) {
					$stat['mtime'] = $last_modified;
				}
                	}

			$info = $this->current_team_info($gender,$division,$season,$conf_code,$team_name);

			return Response::json(array('success' => 1, 'status' => $status, 'data' => $data, 'header' => $header, 'weights' => $weights, 'info' => $info, 'page_title' => $page_title, 'updated' => "Last Updated: ".date("F d, Y, h:i:s A",$stat['mtime']), 'footer' => "", 'prehead' => [],'refreshTime' => $stat['mtime']));
		}
		return Response::json(array('success' => 0, 'status' => 'final', 'message' => "Currently not available", 'data' => [], 'header'=> [], 'weights' => [], 'info' => [], 'page_title' => $page_title, 'footer' => "", 'prehead' => []));
        }

	public function org_game($rec,$team_code,$trec,$polldata,$gender,$division,$game_complete) {

		$vteam = [];
                $hteam = [];
		$gm = [];
		$result = "";
		$gdate = $rec->gdate;
                $home = $rec->home;
                $visitor = $rec->visitor;
                $trhm = $trec;
                $trvis = $trec;
		$dm = ['I' => 'I', 'II' => 'III', 'III' => 'III'];

		//if ($rec->vscore != "" ) {var_dump($rec);$game_complete = "Y";}

		if ($game_complete != "N") {
 			if( $rec->vscore == "" ) {$result =  "";} else {$result = "W";}
  	              	if( $rec->vscore == $rec->hscore && $rec->vscore != "") {$result = "T";}

                	switch ($team_code) {
                        	case ($home) :
					if ($rec->vscore > $rec->hscore) {$result = "L";}
                              	  	break;
                     	   	case ($visitor) :
                       	         	if ($rec->hscore > $rec->vscore) {$result = "L";}
                                	break;
				case ("") :
					$result = "";
                                	break;
                	}
		}

		if (property_exists($trec,$home)) {
                        $trhm = $trec->$home;
                }
		if (property_exists($trec,$visitor)) {
			$trvis = $trec->$visitor;
		}

		$gnote = " F ".$rec->ot;
		if ($game_complete == "N") {$gnote = "";}

		$hteam['score']= $result." ".$rec->vscore."-".$rec->hscore." ".$gnote;
                $hteam['code']= $rec->home;
                $record = "(0-0-0)";
		if (property_exists($trhm,$gdate)) {
                	$record = "(".$trhm->$gdate.")";
                } else if (property_exists($trhm,"current")) {
                        $record = "(".$trhm->current.")";
		}
		$hteam['name']= $rec->home_name." ".$record;;
		$hrnk = $this->get_team_rank($polldata,$gender,$dm[$division],$rec->home_name,$rec->sort_date);
		if ($hrnk) {$hteam['name'] =  "(".$hrnk.") ". $rec->home_name . " ".$record;}

                $vteam['code']= $rec->visitor;
                $record = "(0-0-0)";
                if (property_exists($trvis,$gdate)) {
                        $record = "(".$trvis->$gdate.")";
                } else if  (property_exists($trvis,"current")) {
                        $record = "(".$trvis->current.")";
                }

                $vteam['name']= $rec->vis_name." ".$record;;
		$vrnk = $this->get_team_rank($polldata,$gender,$dm[$division],$rec->vis_name,$rec->sort_date);
                if ($vrnk) {$vteam['name'] =  "(".$vrnk.") ". $rec->vis_name . " ".$record;}
	
		$gm['hgame_info'] = $rec->sort_date;
		$gm['arena_name'] = $rec->arena_name;
		$gm['note'] = $rec->note;
		$gm['tv'] = $rec->game_day." ".$rec->game_type." ".$rec->gametracker;
		$gm['game_id'] = $rec->game_id;
		$gm['result'] = $result;
		$gm['hrnk'] = $hrnk;
		$gm['vrnk'] = $vrnk;
                $gm['hconf'] = $rec->hconf;
                $gm['vconf'] = $rec->vconf;
		$gm['gdate'] = $rec->gdate;
		$gm['type'] = $rec->type;
		$gm['hscore'] = $rec->hscore;
                $gm['vscore'] = $rec->vscore;
		$gm['gnote'] = $gnote;
		if ($game_complete == "N") {
			$gm['gnote'] = $rec->game_day;;
		}

		$hometeam = $this->team_map($gender,$home);
                $visteam = $this->team_map($gender,$visitor);

		if ($hometeam != null) {
			$gm['home_chs_team_code'] = strtoupper($hometeam->chs_team_code);
                } else {
			$gm['home_chs_team_code'] = strtoupper($home);
		}
		if ($visteam != null) {
			$gm['vis_chs_team_code'] =  strtoupper($visteam->chs_team_code);
		} else {
                        $gm['vis_chs_team_code'] = strtoupper($visitor);
                }

		return array($hteam,$vteam,$gm);
	}

	private function livescoredata($gender,$division,$gdate) {

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

 	public function gamedaydata($gender,$division,$gdate,$gameID) {

		date_default_timezone_set('US/Eastern');

                $full_season = $this->get_current_full_season();
                $season = $this->get_season($full_season);

                $filename = $this->get_jsonfilename("scoreboard","composite",$gender,$division,$season);

                $page_title = $full_season." Schedule and Results";

                $box = $this->season_box_file($gender,$season);
                $recaps = $this->season_recap_file($gender,$season);
                $polldata = $this->poll_data();

		list($livescoredata,$lsRefreshTime) = $this->livescoredata($gender,$division,$gdate);
		$scroll_status = "final";

                if (file_exists($filename)) {
                        $file = file_get_contents($filename);
                        $stat = stat($filename);
			$refreshTime = $stat['mtime'];

                        $json = json_decode($file);
                        $data = [];
			$data['all'] = [];
                        $header = ["Score","Home Code","Home","Vis Code","Visitor","Game Info","Arena","Note","TV","gameID","Res","Home Rnk","Vis Rnk","Home Conf Code","Vis Conf Code","Game Date","Game Conf","Home Score","Vis Score","Game Note","Home CHS Code","Vis CHS Code","Last Modified"];
                        $weights = [];
                        $team_name = "";

			$gdates = [];
			$gtype = [];
			$game_info = [];
			$index = [];
			$index['all'] = 0;

			usort($json->data, function ($item1, $item2) {
                                return strcmp($item1->starttime,$item2->starttime);
			});

                        foreach ($json->data as $rec) {
				$gdates[$rec->gdate] = $rec->sort_date;
                                if ($rec->gdate != $gdate) {continue;}

				# take live scored from live scores file
				$game_id = $rec->game_id;

				if(!array_key_exists('all',$index)){$index['all'] = 0;}
				if(!array_key_exists('t20',$index)){$index['t20'] = 0;}

				if(!array_key_exists($rec->type,$index)){$index[$rec->type] = 0;}
				$rec->index = $index[$rec->type];
				if ($gameID == $game_id) {$game_info = $rec;}
				$gtype[$rec->type] = $rec->type;

				$game_complete = "N";
                                if ($rec->vscore != "") {
                                        $game_complete = "Y";
                                }
				list($last_modified,$game_complete,$scroll_status) = $this->livescore_replace($livescoredata,$game_id,$rec,$scroll_status,$game_complete);
			        list ($hteam,$vteam,$gm) = $this->org_game($rec,"",$json->trec,$polldata,$gender,$division,$game_complete);

                                $game = [];

                                foreach ($hteam AS $ind => $rech) {
                                        array_push($game,$hteam[$ind]);
                                }
                                foreach ($vteam AS $ind => $recv) {
                                        array_push($game,$vteam[$ind]);
                                }
                                foreach ($gm AS $ind => $recg) {
                                        array_push($game,$gm[$ind]);
                                }
				array_push($game,$last_modified);

				if(!array_key_exists($rec->type,$data)) {$data[$rec->type] = [];}
                                array_push($data[$rec->type],$game);
				$index[$rec->type]++;

				if (preg_match('/nc|ex/',$rec->type)) {
					$hconf = $gm['hconf'];
					$gtype[$hconf] = $hconf;
					if(!array_key_exists($hconf,$index)){$index[$hconf] = 0;}
					$index[$hconf]++;
					if(!array_key_exists($hconf,$data)) {$data[$hconf] = [];}
					array_push($data[$hconf],$game);
					$vconf = $gm['vconf'];
					if ($hconf != $vconf) {
						$gtype[$vconf] = $vconf;
						if(!array_key_exists($vconf,$index)){$index[$vconf] = 0;}
						$index[$vconf]++;
						if(!array_key_exists($vconf,$data)) {$data[$vconf] = [];}
						array_push($data[$vconf],$game);
					}
				}

				array_push($data['all'],$game);

				if ($gm['vrnk'] > 0 || $gm['hrnk']) {
					if(!array_key_exists('t20',$data)) {$data['t20'] = [];}
					array_push($data['t20'],$game);
					$index['t20']++;
				}

				$index['all']++;
                        }

			$count = count($data['all']);

			if ($count < 5) {
				foreach ($data AS $conf => $gm) {
					if ($conf != "all") {
						unset($data[$conf]);
						unset($gtype[$conf]);
					}
				}	
			}

			$dates = array_values($gdates);
                        sort($dates);

			list ($confarray) =  $this->conf_list_data($gender,$division);

			$conf_list = [];

			if (array_key_exists('t20',$data)) {
                           	$c = [];
                                $c['code'] = "t20";
                                $c['shortname'] = "Top 20 Teams";
                                $c['nice'] = "t20";
                                array_push($conf_list,$c);
                        }

			if ($index['all'] > 0 ) {
                                $c = [];
                                $c['code'] = "all";
                                $c['shortname'] = "All Games";
                                $c['nice'] = "all";
                                array_push($conf_list,$c);
                        }

			foreach ($confarray AS $conf) {
				if (array_key_exists($conf['code'],$gtype)) {
					$c = [];
					$c['code'] = $conf['code'];
                               		$c['shortname'] = $conf['shortname']; 
					$c['nice'] = $conf['nice'];
					array_push($conf_list,$c);
				}
			}

			if (array_key_exists("nc",$gtype)) {
				$c = [];
                                $c['code'] = "nc";
                                $c['shortname'] = "Non-Conference";
                                $c['nice'] = "nonconference";
                                array_push($conf_list,$c);
			}
			if (array_key_exists("ex",$gtype)) {
				$c = [];
                                $c['code'] = "ex";
                                $c['shortname'] = "Exhibition";
                                $c['nice'] = "exhibition";
                                array_push($conf_list,$c);
                        }

			return array(1, $scroll_status, $conf_list, $data, $game_info, $header, $weights, $dates, $page_title, "Last Updated: ".date("F d, Y, h:i:s A",$stat['mtime']),$refreshTime,$lsRefreshTime);
		}

		return array(0,"final",[], [], [], [], [], "", "","");
	}

	public function notificationdata($gender,$division,$team_code) {

	        date_default_timezone_set('US/Eastern');
		$cur_gdate = date('Ymd');
                $notificationfile = config('services.JSON_DIR').$gender."/box/".$cur_gdate."/notifications.json";

		$refresh_secs = 60*60*24;
		$success = 0;
		$last_modified = 0;
		$team_notification = [];

		if (file_exists($notificationfile)) {
                        $file = file_get_contents($notificationfile);
                        $stat = stat($notificationfile);
			$last_modified = $stat['mtime'];
                        $json = json_decode($file);
		
			if (property_exists($json,$team_code)) {
				$team_notification = $json->$team_code;
				$success = 1;
				# is game live
				if ($json->filter->game->$team_code == 0) {
					$refresh_secs = 60*2;
				}
			} else {
				$refresh_secs = $this->test_gameday($gender,$division,$cur_gdate,$team_code);
			}
		} else {
			$refresh_secs = $this->test_gameday($gender,$division,$cur_gdate,$team_code);
		}

		return array($success,$refresh_secs,$team_notification,$last_modified);
	}

	function test_gameday($gender,$division,$gdate,$team_code) {

		$gdate = "20190411";

		list($success, $scroll_status, $conf_list, $data, $game_info, $header, $weights, $dates, $page_title, $updated,$refreshTime) = $this->gamedaydata($gender,$division,$gdate,"");

		# test if any games are today
		if (count($data) == 0) {
			return 60*60*24;
		} else {
			# test if team plays
			$pass = 0;
			foreach ($data AS $ttype => $rec) {
				foreach ($rec AS $ind => $game) {
					if (($game[1] == $team_code || $game[3] == $team_code) && $game[22] != 0) {
						$pass = 1;
					}
				}
			}

			if ($pass == 1) {return 60*10;}
		}

		return 60*60*24;
	}
	
	function livescore_replace($livescoredata,$game_id,$rec,$scroll_status,$game_complete) {
	
		$last_modified = 0;

        	if (property_exists($livescoredata,$game_id)) {
                	$lrec = $livescoredata->$game_id;

			$minutesot = 5;
		        $countdown = 0;
			$teamsflip = 0;
                        if (property_exists($lrec,"minutesot")) {$minutesot = $lrec->minutesot;}
                        if (property_exists($lrec,"countdown")) {$countdown = $lrec->countdown;}
			if (property_exists($lrec,"teamsflip")) {$teamsflip = $lrec->teamsflip;}

                        if ($teamsflip) {
                        	$hscore = $lrec->hscore;
                                $lrec->hscore = $lrec->vscore;
                                $lrec->vscore = $hscore;
                        }
 
                        if ($lrec->complete == "N" && $game_complete == "N") { //  && $rec->vscore == "") {
                                $scroll_status = "live";
			}
                        if ($rec->vscore == "" || $scroll_status == "live") {
                                $rec->last_play = "";
                                if (property_exists($lrec,"last_play")) {$rec->last_play = $lrec->last_play;}
                                $rec->pp = "";
                                if (property_exists($lrec,"pp")) {$rec->pp = $lrec->pp;}
                                $rec->hscore = $lrec->hscore;
                                $rec->vscore = $lrec->vscore;
                                $rec->last_modified = $lrec->last_modified;
                                $last_modified = $lrec->last_modified;
                                $rec->game_day = $this->game_clock($lrec->clock,$lrec->period,$minutesot,$countdown)." P".$lrec->period;
                        }
                }

		return array($last_modified,$game_complete,$scroll_status);
	}

	private function game_clock($clock,$period,$minutesot,$countdown) {
		if (preg_match('/:/',$clock)) {
                	list($min,$sec) = explode(":",$clock);
                } else {
                        $min = $clock;
                        $sec = 0;
                }

		if ($countdown) {return $clock;}
	
		$pertime = 20;
 		if ($period>3) {$pertime = $minutesot;}

		$game_clock = ($pertime-1-$min).":".substr(160-$sec,1,2);

		if ($sec == 0) {
			 $game_clock = ($pertime-$min).":00";
		}

		return $game_clock;		
	}

        public function gameday($division,$full_gender,$conf_code,$gdate,$gameID,$oldRefreshTime,$oldScrollRefreshTime) {

		$gender = $this->get_gender($full_gender);
		$division = strtoupper($division);

		list($success, $scroll_status, $conf_list, $data, $game_info, $header, $weights, $dates, $page_title, $updated,$refreshTime,$scrollRefreshTime) = $this->gamedaydata($gender,$division,$gdate,$gameID);

                if ($oldRefreshTime != 0 && $scrollRefreshTime == $oldRefreshTime) {
                        return Response::json(array('success' => 2, 'refreshTime' => $refreshTime, 'scroll_status' => $scroll_status));
		}

                return Response::json(array('success' => $success, 'scroll_status' => $scroll_status, 'conf' => $conf_list, 'data' => $data, 'header' => $header, 'weights' => $weights, 'dates' => $dates, 'page_title' => $page_title, 'updated' => "", 'footer' => "", 'prehead' => [],'refreshTime' => $scrollRefreshTime));
        }

	public function notification($gender,$division,$conf_code,$team_code,$gdate,$refreshTime,$oldScrollRefreshTime,$boxMode) {
                list($success, $refresh_secs, $data, $last_modified) = $this->notificationdata($gender,$division,$team_code);
                return Response::json(array('success' => $success, 'refresh_secs' => $refresh_secs, 'data' => $data,'refreshTime' => $last_modified));
        }

	public function gamedaybox($division,$full_gender,$conf_code,$gdate,$gameID,$oldRefreshTime,$oldScrollRefreshTime,$boxMode) {

                $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
		
		# all modes require gameday scroll data
		list($success, $scroll_status, $conf_list, $data, $game_info, $header, $weights, $dates, $page_title, $updated,$refreshTime,$scrollRefreshTime) = $this->gamedaydata($gender,$division,$gdate,$gameID);

		$scroll = [];
		if ($oldRefreshTime != 0 && $scrollRefreshTime == $oldScrollRefreshTime) {
			$scroll = ['conf' => [], 'scroll_status' => $scroll_status, 'data' => [], 'header' => [], 'weights' => [], 'dates' => [], 'page_title' => "", 'updated' => "", 'footer' => "", 'prehead' => []];
                } else {
			$cdates = [];
			if ($oldRefreshTime == 0) {$cdates=$dates;}
			
			$scroll = ['conf' => $conf_list, 'scroll_status' => $scroll_status, 'data' => $data, 'header' => $header, 'weights' => $weights, 'dates' => $cdates, 'page_title' => $page_title, 'updated' => $updated, 'footer' => "", 'prehead' => []];
		}

		$live_box = [];
		$oppcolumns = [];
		$preheader = [];
		$colgroup = [];

		if (!isset($game_info)) {
			 return Response::json(array('success' => 0, 'status' => "no game info", 'scroll' => [], 'game_info' => [], 'header' => [], 'data' => [], 'oppcolumns' => [], 'weights' => [], 'preheader' => [], 'colgroup' => []));
		}

		$ghead = $data['all'][$game_info->index];
		//$game_info->type][$game_info->index];

		# load box file
		$home= $this->team_map($gender,$game_info->home);
		$vis= $this->team_map($gender,$game_info->visitor);
		if ($vis != "") {
			$match = $vis->nice."-vs-".$home->nice;
		} else {
			$match = "-vs-".$home->nice;	
		}	
		$boxfile = $this->get_jsonboxfilename($match,$gender,$gdate);
		$stat = stat($boxfile);
		$refreshTime = $stat['mtime'];

		if ($oldRefreshTime != 0 && $oldRefreshTime == $refreshTime) {
			return Response::json(array('success' => 2, 'status' => $scroll_status, 'scroll' => $scroll, 'refreshTime' => $refreshTime, 'scrollRefreshTime' => $scrollRefreshTime));
		}

		$status = "none";
		if (file_exists($boxfile)) {
                        $json = file_get_contents($boxfile);
                        $box = json_decode($json);
			if (property_exists($box,"goals")) {
                                $status = "final";
			}
			list($header,$data,$oppcolumns,$weights,$preheader,$colgroup) = $this->organize_struct($box,$status,$boxMode);

			if (property_exists($box,"goals") && preg_match('/box|all/',$boxMode) ) {
				list($header,$data,$oppcolumns,$weights,$preheader,$colgroup) = $this->organize_box($box,$header,$data,$oppcolumns,$weights,$preheader,$colgroup);
			}
			if (property_exists($box,"livebox")) {
				if ($status == "none") {$status = "live";}
			//	$status = "live";
				list($header,$data,$oppcolumns,$weights,$preheader,$colgroup) =  $this->organize_livebox($box,$game_info,$status,$header,$data,$oppcolumns,$weights,$preheader,$colgroup,$boxMode);
		//		$live_box = $box->livebox;
                        }
		} else {
			$success = 0;
		}
		
		return Response::json(array('success' => $success, 'status' => $status, 'scroll' => $scroll, 'game_info' => $game_info, 'header' => $header, 'data' => $data, 'oppcolumns' => $oppcolumns, 'weights' => $weights, 'preheader' => $preheader, 'colgroup' => $colgroup, 'refreshTime' => $refreshTime, 'scrollRefreshTime' => $scrollRefreshTime));
	}

	# eg /home/json/m/box/20140215/minnesota-vs-michigan.json
        public function get_jsonboxfilename($match,$gender,$gdate)
        {
                return config('services.JSON_DIR').$gender."/box/".$gdate."/".$match.".json";
        }

	public function organize_livebox($box,$game_info,$status,$header,$data,$oppcolumns,$weights,$preheader,$colgroup,$boxMode) 
	{
		$live_box = $box->livebox;

		if (preg_match('/box|all/',$boxMode)) { 
			# edit ref and aref in header
			if ($status == "live") {
				$game_info->ref = $live_box->game_info->ref;
				$game_info->aref = $live_box->game_info->aref;
				list($header,$data,$oppcolumns,$weights,$preheader,$colgroup) = $this->organize_box($live_box,$header,$data,$oppcolumns,$weights,$preheader,$colgroup);
			}
		}

		if (preg_match('/line|all/',$boxMode)) {  
			$lines = [];

			$ldata = $live_box->teams->lineup;

               		foreach($ldata as $tc => $linea) {
				$lines[$tc] = [];
				$lines[$tc]['lines'] = [];
       		                foreach($linea->lines as $ind => $line) {
       	                        	$rec = [];
       	                        	array_push($rec,"LW: ".$line->lw);
					array_push($rec,"C: ".$line->c);
					array_push($rec,"RW: ".$line->rw);
                                	array_push($lines[$tc]['lines'],$rec);
                        	}

				foreach($linea->lines as $ind => $line) {
					if ($line->ld != "") {
                                		$rec = [];
                                		array_push($rec,"LD: ".$line->ld);
                                		array_push($rec,"RD: ".$line->rd);
                                		array_push($rec,"G: ".$line->g);
                                		array_push($lines[$tc]['lines'],$rec);
					}
                        	}
               	 	}

                	$data['lineup'] = $lines;
		}

		if (preg_match('/play|all/',$boxMode)) {
			$plays = [];

			foreach($live_box->plays as $period => $playa) {
				foreach($playa as $ind => $ply) {
					$rec = [];
					array_push($rec,$ply->number);
					array_push($rec," P".$period." ".substr($ply->text,0,100));
					array_push($plays,$rec);
				}
                	}

			usort($plays, function ($item1, $item2) {
                       	 	return $item2['0'] <=> $item1['0'];
                	});

			$data['plays'] = $plays;
		}

		return array($header,$data,$oppcolumns,$weights,$preheader,$colgroup);	
	}

	public function get_ots($box) {
		$ots = 0;
                if (property_exists($box,"header")) {
                        if ($box->header->ots != "") {
                                $ots = $box->header->ots;
                        }
		}
		return $ots;
	}

	public function organize_struct($box,$status,$boxMode) {
	
		$header = [
			'byperiod' => ['Team'],
			'goals' => ['Per','Team','Assist(s)','Type'], 
			'penalties' => ['Per','Team','Infraction','Time'], 
			'teamscore' => ['Team','Player','Pts'], 
			'teampen' => ['Team','Player','Pens'], 
			'goaltending' => ['Team','Time'],
			'plays' => ['ID',' Description'],
			'lines' => ['Player 1','Player 2','Player 3']
		];

                $preheader = [
			'byperiod' => ['','Gls',''],
                        'goals' => ['','','Scorer','Time'],
                        'penalties' => ['','','Player',''],
                        'teamscore' => [],
                        'teampen' => [],
                        'goaltending' => ['','Goalie','Svs'],
			'plays' => [],
			'lines' => []
                ];

		$percnt = 3;
		if ($status != "final") {
			$percnt = count($box->livebox->per);
		}
		$numper = $percnt + $this->get_ots($box);

		for ($per = 1; $per < $numper + 1; $per++) {
			array_push($header['goaltending'],$per);
			array_push($preheader['goaltending'],'');
			array_push($header['byperiod'],$per);
                        array_push($preheader['byperiod'],'');
		}
		
		array_push($preheader['goaltending'],"");
		array_push($header['goaltending'],"Tot");
		array_push($header['goaltending'],"GA");

		array_push($preheader['byperiod'],'Power');
                array_push($header['byperiod'],"Tot");
                array_push($header['byperiod'],"Pen");
		array_push($header['byperiod'],"Plays");

		$data = [
			'byperiod' => [],
                        'goals' => [],
                        'penalties' => [],
                        'teamscore' => [],
                        'teampen' => [],
                        'goaltending' => [],
			'plays' => []
                ];

		$oppcolumns = [
			'byperiod' => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
                        'goals' => array(0,0,0,0,0,0),
                        'penalties' => array(0,0,0,0,0,0,0),
                        'teamscore' => array(0,0,0),
                        'teampen' => array(0,0,0),
                        'goaltending' => array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0),
			'plays' => array(0,0),
			'lines' => array(0,0,0,0)
                ];

		$colgroup = [
                        'goals' => array(1,1,2,1,2,1),
			'goaltending' => array(1,2,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1),
			'penalties' => array(1,1,2,1,1)
                ];

		$weights = [
			'byperiod' => array("1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f"),
                        'goals' => array("1.0f","1.0f","1.0f","1.0f","1.0f","1.0f"),
                        'penalties' => array("1.0f","1.0f","1.0f","1.0f","1.0f"),
                        'teamscore' => array("1.0f","1.0f","1.0f"),
                        'teampen' => array("1.0f","1.0f","1.0f"),
                        'goaltending' => array("1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f","1.0f"),
			'plays' => array("1.0f","100.0f"),
			'lines' => array("1.0f","1.0f","1.0f","1.0f")
                ];

		return array($header,$data,$oppcolumns,$weights,$preheader,$colgroup);
	}

	public function organize_box($box,$header,$data,$oppcolumns,$weights,$preheader,$colgroup) {
		$data['goals'] = $this->org_data("goals", $box, ["period","goalcode","scorer","assist1","time","assist2"],0);
		$data['penalties'] = $this->org_data("penalties", $box, ["period","pencode","name","infraction","time","pims"],0);
		$data['teamscore'] = $this->org_data("teamscore", $box, ["chs_team_code","name","a"],0);
		$data['teampen'] = $this->org_data("teampen", $box, ["chs_team_code","name","pen"],0);

		foreach ($box->summary as $rec) {
                        $res = [];

                        array_push($res,$rec->chs_team_code);

                        foreach ($box->per as $per) {
				if (property_exists($rec->goals,$per)) {
                                	array_push($res,$rec->goals->$per);
				} else {
					array_push($res,0);
				}
                        }

                        array_push($res,$rec->tgoals);
                        array_push($res,$rec->tpen."-".$rec->tpim);
			array_push($res,$rec->tppg."-".$rec->tppo);

                        array_push($data['byperiod'],$res);
                }

		foreach ($box->goalies as $rec) {
			$res = [];

			array_push($res,$rec->goaliecode);
			array_push($res,$rec->name);
			array_push($res,"(".$rec->time." ".$rec->decision.")");

			foreach ($box->per as $per) {
				if (property_exists($rec,$per)) {
					array_push($res,$rec->$per);
				} else {
					array_push($res,"");
				}
			}

			array_push($res,$rec->tsaves);
			array_push($res,"(".$rec->tga. " GA)");

			array_push($data['goaltending'],$res);
		}

		return array($header,$data,$oppcolumns,$weights,$preheader,$colgroup);
	}

	function org_data($key, $box, $columns,$track_period) {
		$data = [];

		$period = 0;
		foreach ($box->$key as $rec) {
			if ($track_period) {
				if ($period != $rec->period) {
					$period = $rec->period;
					$res = array("Per ".$period);
       			                for($i=1;$i<count($columns);$i++) {
                       				$rkey = $columns[$i];
                      		         	array_push($res,"");
                        		}
					array_push($data,$res);
				}
			}
                        $res = array();

                        for($i=0;$i<count($columns);$i++) {
                                $rkey = $columns[$i];
                                array_push($res,$rec->$rkey);
                        }
			$method = "extra_$key";
			if(method_exists($this,$method)) {$res = $this->$method($rec,$res);}
                        array_push($data,$res);
                }
		return $data;
	}

	function extra_goals($rec,$res) {
		if ($res[5] != "") {$res[3] = $res[3].", ".$res[5];}

                if (property_exists($rec,"goal_type")) { $res[5] = $rec->goal_type;} else {$res[5] = "";}
                if ($rec->iseng) {$res[5] = "ENG ".$res[5];}
                if ($rec->isppg) {$res[5] = "PPG ".$res[5];}
                if ($rec->isshg) {$res[5] = "SHG ".$res[5];}
                if ($rec->isgwg) {$res[5] = "GWG ".$res[5];}
		return $res;
	}

	function extra_teamscore($rec,$res) {
		$res[2] = $rec->g."-".$rec->a.'='.$rec->pts;
                return $res;
        }

	function extra_penalties($rec,$res) {
                $res[3] = $res[3]." (".$res[5]." min)";
		unset($res[5]);
                return $res;
        }

	function extra_teampen($rec,$res) {
                $res[2] = $rec->pen."-".$rec->pims;
                return $res;
        }
}
