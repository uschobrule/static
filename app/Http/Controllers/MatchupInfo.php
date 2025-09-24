<?php

namespace App\Http\Controllers;
use Response;

class MatchupInfo extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "matchup";
		$full_season = $this->get_current_full_season();
                $gender = $this->get_gender($parts[0]);
		$season = $this->get_season($full_season);
		$game_id = $parts[1];
		
		return array($template,$gender,$season,$full_season,$game_id);
	}
	public function matchup($gender,$division,$full_season,$game_id) 
	{
		$template = "matchup";
		$season = $this->get_season($full_season);
		# parse_uri
		//list ($template,$gender,$season,$full_season,$game_id) = $this->parseuri($uri);

		$data = $this->data($gender,$season,$game_id);
		$html = view("json/$template", ['data' => $data])->render();

		return Response::json(array('html' => $html));
	}
	public function data($gender,$season,$game_id){
//		echo "$gender,$season,$game_id";
		$con = $this->_init_db();

                date_default_timezone_set('US/Eastern');
                $cur_time =  date('Y-m-d H:i:s');

		$sql = "
SELECT g.rowid game_id, ch.shortname home_team, cv.shortname vis_team, g.hdiv, g.neutral
FROM gd$season.".$gender."games g
JOIN db_perpetual.".$gender."schoolYxY ch ON g.home = ch.code AND ch.start_year <= $season AND ch.end_year > $season
JOIN db_perpetual.".$gender."schoolYxY cv ON g.visitor = cv.code AND cv.start_year <= $season AND cv.end_year > $season
WHERE g.rowid = $game_id";

//		echo $sql;

                $game_res=mysqli_query($con,$sql);
		$json = [];

                if(mysqli_num_rows($game_res)>0) {
			$d = mysqli_fetch_object($game_res);

			$home_team = $d->home_team;
			$vis_team = $d->vis_team;
			$hdiv = $d->hdiv;

			$json['home_team'] = $home_team;
			$json['vis_team'] = $vis_team;
			$json['neutral'] = $d->neutral;
		
			$pwrfilename = $this->get_jsonfilename("ranking","pwrraw",$gender,$hdiv,$season,"sc");
			$rpifilename = $this->get_jsonfilename("ranking","rpiraw",$gender,$hdiv,$season,"sc");

		        # poll is all in one file
                	$poll_json_obj = $this->poll_data();
                	$polldate = $poll_json_obj->poll_data->$gender->$hdiv->current->PollDate;
			$polldata = $poll_json_obj->data->$gender->$hdiv->$polldate;

			$json['poll'] = [];
			$poll = [];

			foreach ($polldata->data as $rec) {
				$poll[$rec->shortname] = $rec->rnk;
			}

			$json['poll'] = $poll;

               		if (file_exists($pwrfilename)) {
                       		$jsont = file_get_contents($pwrfilename);
				$json_obj = json_decode($jsont);

				$json['pwr'] = [];
				$json['pwr'][$home_team][$vis_team] = $json_obj->$home_team->$vis_team;
				$json['pwr'][$home_team]['winpct'] = $json_obj->$home_team->winpct;
				$json['pwr'][$home_team]['record'] = $json_obj->$home_team->record;
				//$json['pwr'][$home_team]['pts'] = $json_obj->raw_data->$home_team->pts;
				$json['pwr'][$home_team]['rnk'] = $json_obj->$home_team->rnk;
				$json['pwr'][$home_team]['adjrpiqwb'] = $json_obj->$home_team->adjrpiqwb;
				$json['pwr'][$home_team]['rpirnk'] = $json_obj->$home_team->rpirnk;

				$json['pwr'][$vis_team][$home_team] = $json_obj->$vis_team->$home_team;
                                $json['pwr'][$vis_team]['winpct'] = $json_obj->$vis_team->winpct;
                                $json['pwr'][$vis_team]['record'] = $json_obj->$vis_team->record;
				//$json['pwr'][$vis_team]['pts'] = $json_obj->raw_data->$vis_team->pts;
                                $json['pwr'][$vis_team]['rnk'] = $json_obj->$vis_team->rnk;
                                $json['pwr'][$vis_team]['adjrpiqwb'] = $json_obj->$vis_team->adjrpiqwb;
				$json['pwr'][$vis_team]['rpirnk'] = $json_obj->$vis_team->rpirnk;
			}

			if (file_exists($rpifilename)) {
                        	$jsont = file_get_contents($rpifilename);
				$json_obj = json_decode($jsont);
	
				$json['rpi'] = [];
				$json['rpi'][$home_team] = $json_obj->$home_team;
                                $json['rpi'][$vis_team] = $json_obj->$vis_team;
			}
		}
		return $json;
	}
}
