<?php

namespace App\Http\Controllers;

use Response;

class BoxController extends ScoreboardController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "box";
		$gender = "";
		$match = "";
		$division = "";
		$box_view = "";
		$refreshTime = 0;

		if ($count > 0) $gender = $this->get_gender($parts[0]);
		if ($count > 1) $year = $parts[1];
		if ($count > 2) $month = $parts[2];
		if ($count > 3) $day = $parts[3];
		if ($count > 4) $match = $parts[4];
		if ($count > 5) $box_view = $parts[5];
		if ($count > 6) $refreshTime = $parts[6];

		$gdate = $year.$month.$day;

		return array($template,$match,$gender,$division,$gdate,$box_view,$refreshTime);
	}

	# eg /home/json/m/box/20140215/minnesota-vs-michigan.json
	public function get_jsonfilename($template,$match,$gender,$division,$gdate) 
	{
		return config('services.JSON_DIR').$gender."/".$template."/".$gdate."/".$match.".json";
	}
	
	public function box($full_gender,$year,$month,$day,$match,$box_view,$oldRefreshTime)
	{
		$gender = $this->get_gender($full_gender);
		$template = "box";
		$division = "";
		$gdate = $year.$month.$day;

        //        list($template,$match,$gender,$division,$gdate,$box_view,$oldRefreshTime) = $this->parseuri($uri);

                $filename = $this->get_jsonfilename($template,$match,$gender,$division,$gdate);
		if (file_exists($filename)) {
			$stat = stat($filename);
			$refreshTime = $stat['mtime'];

		       	$json = file_get_contents($filename);
			$json_obj = json_decode($json);

			$status = "unknown";
			if (property_exists($json_obj,"goals") && !property_exists($json_obj,"err_msg")) {
                               	$status = "final";
			} elseif (property_exists($json_obj,"livebox")) {
				$status = "live";
				if (property_exists($json_obj->livebox,"game_info")) {
					if ($json_obj->livebox->game_info->complete == "Y" && property_exists($json_obj,"header") && !property_exists($json_obj,"err_msg")) {
						$status = "final";	
					}
				} else {
					$status = "unknown";
				}
			}

			if ($oldRefreshTime != 0 && $refreshTime == $oldRefreshTime) {
                                return Response::json(array('status' => $status, 'html' => '', 'boxstats' => '', 'lines' => '', 'plays' => '', 'refreshTime' => $refreshTime ));
                        }

			$html = "";

			if (preg_match('/box|all/',$box_view)) {
				if ($status == "live") {
					if ($json_obj->livebox->game_info->complete == "Y") {
						$json_obj->livebox->game_info->clock = "00:00";
					}
	
					$json_obj->livebox->header->pp_msg = "";
					if (property_exists($json_obj->livebox->game_info,"pp") && $json_obj->livebox->game_info->complete == "N") {
						if (property_exists($json_obj->livebox->game_info->pp,"pp_msg")) {
							$json_obj->livebox->header->pp_msg = $json_obj->livebox->game_info->pp->pp_msg." ".$json_obj->livebox->game_info->pp->pp_sub_msg;
						}
					}

					$countdown = 0;
					if (!property_exists($json_obj->livebox,"play")) {$json_obj->livebox->plays = [];}
					if (!property_exists($json_obj->livebox,"per")) {$json_obj->livebox->per[1] = 1;}
					if (property_exists($json_obj->livebox->game_info,"countdown")) {$countdown = $json_obj->livebox->game_info->countdown;}
					if (!property_exists($json_obj->livebox->game_info,"clock")) {$json_obj->livebox->game_info->clock = "20:00";}
					$json_obj->livebox->game_info->pd_time = $this->pd_countdown_time($json_obj->livebox->game_info->period,$json_obj->livebox->game_info->clock,5,$countdown);
					$html = view("json/livebox", ['json' => $json_obj->livebox])->render();
                               	 	$html = preg_replace( "/\r|\n|\t|/", "", $html );
				} else {
					if (!property_exists($json_obj,"per")) { 
						$json_obj->per = [];
						var_dump($json_obj->per);
					} elseif ($json_obj->per == null) {
						$json_obj->per = [];
					} else {
						$pera = [];
						foreach ($json_obj->per AS $ind => $per) {
							if ($per != "") {
								array_push($pera,$per);
							}
						}
						$json_obj->per = $pera;
					}

					$ext = "";
					if ($json_obj->header->ots != "") {$ext=" OT".$json_obj->header->ots;}
					$json_obj->header->game_result = $json_obj->header->vis_name." ".$json_obj->header->vscore.", ".$json_obj->header->home_name." ".$json_obj->header->hscore." F".$ext;

					$html = view("json/box", ['json' => $json_obj])->render();
			        	$html = preg_replace( "/\r|\n|\t|/", "", $html );
				}
			}

			$playshtml = "";
			$lineshtml = "";
			$statshtml = "";
			$division = "";	

			if (property_exists($json_obj,"boxstats")) {
				$statshtml = view("json/boxstats", ['boxstats' => $json_obj->boxstats, 'teams' => $json_obj->teams, 'V' => $json_obj->teams->teamInfo->V, 'H' => $json_obj->teams->teamInfo->H])->render();
                                $statshtml = preg_replace( "/\r|\n|\t|/", "", $statshtml );
			}

			if (property_exists($json_obj,"livebox")) {
				if (preg_match('/play|all/',$box_view)) {
					$plays = [];
					foreach ($json_obj->livebox->plays AS $period => $recs) {
						foreach ($recs AS $play) {
							$key = $period."-".sprintf('%03d', $play->number);
							$play->period = $period;
							$plays[$key] = $play;
						}
					}

					krsort($plays);

					$playshtml = view("json/plays", ['plays' => $plays])->render();
       	        		        $playshtml = preg_replace( "/\r|\n|\t|/", "", $playshtml );
				} 
				if (preg_match('/stats|all/',$box_view) && property_exists($json_obj->livebox,"sum_players")) {
					$statshtml = view("json/boxstats", ['boxstats' => $json_obj->livebox->sum_players, 'teams' => $json_obj->livebox->teams, 'V' => $json_obj->livebox->teams->teamInfo->V, 'H' => $json_obj->livebox->teams->teamInfo->H])->render();
                                	$statshtml = preg_replace( "/\r|\n|\t|/", "", $statshtml );
				} 
				if (preg_match('/line|all/',$box_view) && property_exists($json_obj->livebox,"teams")) {
					if (property_exists($json_obj->livebox->teams,"lineup")) {
						if (!property_exists($json_obj->livebox->teams->lineup,"H")) {
							$json_obj->livebox->teams->lineup->H = (object)[];
							$json_obj->livebox->teams->lineup->H->lines = (object)[];
						}
						if (!property_exists($json_obj->livebox->teams->lineup,"V")) {
							$json_obj->livebox->teams->lineup->V = (object)[];
							$json_obj->livebox->teams->lineup->V->lines = (object)[];
						}
						$lineshtml = view("json/lines", ['lineup' => $json_obj->livebox->teams->lineup])->render();
						$lineshtml = preg_replace( "/\r|\n|\t|/", "", $lineshtml );
					}
				} else if (preg_match('/line/',$box_view)) {
					$lineshtml = "Not Available";
				}
				if (property_exists($json_obj->livebox,"header")) {
					if (property_exists($json_obj->livebox->header,"hdiv")) {
						$division = $json_obj->livebox->header->hdiv;
	                        		if ($division == "II") {
        	               				$division = $json_obj->livebox->header->vdiv;
                	                		if ($division == "II") { $division == "III";}
						}
					}
				}
			} 

			if ($division == "") {
				$division = $json_obj->header->hdiv;
				if ($division == "II") {
                        		$division = $json_obj->header->vdiv;
                                	if ($division == "II") { $division == "III";}
				}
			}

			$code = "composite";
			$sitecode = [];
			$full_season = $this->get_current_full_season();
			$season = $this->get_season($full_season);
                	$page_title = $this->get_page_title($template,$code,$gender,$division,$season,$sitecode,$full_season);

                	# composite is all in one file
                	$filename = parent::get_jsonfilename("scoreboard","composite",$gender,$division,$season,$sitecode);
                       	$json = file_get_contents($filename);
			$json_obj = json_decode($json);

			list ($data,$datatable,$page_title,$toDate,$fromDate,$minDate,$maxDate,$last_updated,$status) = $this->gameday_template("gameday_template",$json_obj,$code,$page_title,$gender,$division,$full_season,$gdate,$oldRefreshTime);

			return Response::json(array('html' => $html, 'boxstats' => $statshtml, 'plays' => $playshtml, 'lines' => $lineshtml, 'status' => $status, 'refreshTime' => $refreshTime, 'games' => ['game_cnt' => count($data['data']), 'json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'toDate' => $toDate, 'fromDate' => $fromDate, 'minDate' => $minDate, 'maxDate' => $maxDate, 'refreshTime' => $last_updated, 'status' =>  $status]));
		}

		# http://json.uscho.com/livegame.json?gamid=3
		if (1) {
			echo "NONE";
                } else {
                        return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => 'Unknown Page', 'datatable' => ''));
                }
	} 

	public function pd_countdown_time($per,$per_time,$minsot,$countdown) {

		if ($countdown) {return $per_time;}
		if ($per_time == ":") {$per_time = "20:00";}

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
