<?php

namespace App\Http\Controllers;
use Response;

class StatsPivotController extends StatsController
{	
	public function pivot($division,$full_gender,$full_season,$dataset){

		$gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
		$season = $this->get_season($full_season);

		# composite is all in one file
		$filename = $this->get_jsonfilename("stats","pivot-".$dataset,$gender,$division,$season,"");

		if (file_exists($filename)) {
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$method = $dataset."_config";
			$res = $this->$method();
			$res['data'] = $json_obj->data;

			$con = $this->_init_db();
			$res['global'] = $this->loadglobal($con,$dataset);
			return $res;
		}
	}

	private function scoring_config() {
		$res = [
			'pg' => ['g','pts','shots','shotsNet','bl','pim','pencnt'],
			'fields' => [
'values' => ['label' => 'Values (Must be a Column)', 'calc' => 'none ' ,'desc' => 'Represents all the values to help with ordering columns'],
'date' => ['label' => 'DATE', 'calc' => 'count_distinct' ,'desc' => 'The date of the game'],
'year' => ['label' => 'YEAR', 'calc' => 'count_distinct' ,'desc' => 'The calendar year of the game.'],
'month' => ['label' => 'MONTH', 'calc' => 'count_distinct' ,'desc' => 'The month of the game in YYYY-MM format'],
'day' => ['label' => 'DAY', 'calc' => 'count_distinct' ,'desc' => 'The day of the week of the game'],
'week' => ['label' => 'WEEK', 'calc' => 'count_distinct' ,'desc' => 'The week of the game since season started'],
'type' => ['label' => 'GAME TYPE', 'calc' => 'count_distinct' ,'desc' => 'The type of game EX, NC or Conference Code'],
'shortname' => ['label' => 'Team', 'calc' => 'count_distinct' ,'desc' => 'The name of the team for the player detail'],
'opp_shortname' => ['label' => 'Opponent', 'calc' => 'count_distinct' ,'desc' => 'The opponent of the Team'],
'game_res' => ['label' => 'Team Rec', 'calc' => 'function' ,'desc' => 'Wether the game Resulted in a W L or T. If used as a Value it formats as W-L-T '],
'confcode' => ['label' => 'TEAM CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the players team'],
'opp_confcode' => ['label' => 'OPP CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the opponent'],
'loc' => ['label' => 'Location', 'calc' => 'count_distinct ' ,'desc' => 'Whether the game is Home or Road or Neutral'],
'player_name' => ['label' => 'PLAYER', 'calc' => 'count_distinct ' ,'desc' => 'The Player'],
'yr' => ['label' => 'YR', 'calc' => 'count_distinct ' ,'desc' => 'The Class of the Athlete Fr, So, Jr, Sr and Gr'],
'pos' => ['label' => 'Pos', 'calc' => 'count_distinct ' ,'desc' => 'Whether the player is a forward (F) of a Defenseman (D)'],
'gp' => ['label' => 'GP', 'calc' => 'count_distinct' ,'desc' => 'Games played', 'count_field' => 'game_ID'],
'g' => ['label' => 'G', 'calc' => 'sum' ,'desc' => 'Goals Scored by Player'],
'a' => ['label' => 'A', 'calc' => 'sum' ,'desc' => 'Assists'],
'pts' => ['label' => 'PTS', 'calc' => 'sum' ,'desc' => 'Total Points'],
'ppg' => ['label' => 'PPG', 'calc' => 'sum' ,'desc' => 'Power Play Goal'],
'gwg' => ['label' => 'GWG', 'calc' => 'sum' ,'desc' => 'Game Winning Goal'],
'shg' => ['label' => 'SHG', 'calc' => 'sum' ,'desc' => 'Shorthanded Goal'],
'fg' => ['label' => 'FG', 'calc' => 'sum' ,'desc' => 'First Goal'],
'eng' => ['label' => 'ENG', 'calc' => 'sum' ,'desc' => 'Empty Net Goal'],
'pim' => ['label' => 'PEN MIN', 'calc' => 'sum' ,'desc' => 'Penalty Minutes'],
'pencnt' => ['label' => 'PEN CNT', 'calc' => 'sum' ,'desc' => 'Number of Penalties'],
'shots' => ['label' => 'SHOT ATTEMPTS', 'calc' => 'sum' ,'desc' => 'Shot Attempts'],
'shotsNet' => ['label' => 'SHOT ON NET', 'calc' => 'sum' ,'desc' => 'Shots on Net'],
'fo' => ['label' => 'TOTAL FACEOFF', 'calc' => 'sum' ,'desc' => 'Total Faceoffs'],
'foW' => ['label' => 'FACEOFF WINS', 'calc' => 'sum' ,'desc' => 'Faceoff Wins'],
'bl' => ['label' => 'BLOCKS', 'calc' => 'sum' ,'desc' => 'Blocked Shots'],
'plsmns' => ['label' => 'PLSMNS', 'calc' => 'sum' ,'desc' => 'Plus Minus'],
'opp_score' => ['label' => 'Opponent Score', 'calc' => 'sum' ,'desc' => 'Opponents Score'],
			]
		];
		return $res;
	}

	private function goaltending_config() {
		return  [
                        'pg' => ['ga','saves','shotsAllowed'],
			'fields' => [
'values' => ['label' => 'Values (Must be a Column)', 'calc' => 'none ' ,'desc' => 'Represents all the values to help with oredreing columns'],
'date' => ['label' => 'DATE', 'calc' => 'count_distinct' ,'desc' => 'The date of the game'],
'year' => ['label' => 'YEAR', 'calc' => 'count_distinct' ,'desc' => 'The calendar year of the game.'],
'month' => ['label' => 'MONTH', 'calc' => 'count_distinct' ,'desc' => 'The month of the game in YYYY-MM format'],
'day' => ['label' => 'DAY', 'calc' => 'count_distinct' ,'desc' => 'The day of the week of the game'],
'week' => ['label' => 'WEEK', 'calc' => 'count_distinct' ,'desc' => 'The week of the game since season started'],
'type' => ['label' => 'GAME TYPE', 'calc' => 'count_distinct' ,'desc' => 'The type of game EX, NC or Conference Code'],
'shortname' => ['label' => 'Team', 'calc' => 'count_distinct' ,'desc' => 'The name of the team for the player detail'],
'opp_shortname' => ['label' => 'Opponent', 'calc' => 'count_distinct' ,'desc' => 'The opponent of the Team'],
'game_res' => ['label' => 'Team Rec', 'calc' => 'function' ,'desc' => 'Wether the game Resulted in a W L or T. If used as a Value it formats as W-L-T '],
'confcode' => ['label' => 'TEAM CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the players team'],
'opp_confcode' => ['label' => 'OPP CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the opponent'],
'loc' => ['label' => 'Location', 'calc' => 'count_distinct ' ,'desc' => 'Whether the game is Home or Road or Neutral'],
'player_name' => ['label' => 'PLAYER', 'calc' => 'count_distinct ' ,'desc' => 'The Player'],
'yr' => ['label' => 'YR', 'calc' => 'count_distinct ' ,'desc' => 'The Class of the Athlete Fr, So, Jr, Sr and Gr'],
'pos' => ['label' => 'Pos', 'calc' => 'count_distinct ' ,'desc' => 'Whether the player is a forward (F) of a Defenseman (D)'],
'gp' => ['label' => 'GP', 'calc' => 'count_distinct' ,'desc' => 'Games played', 'count_field' => 'game_ID'],
'ga' => ['label' => 'GA', 'calc' => 'sum' ,'desc' => 'Goals Allowed'],
'saves' => ['label' => 'SAVES', 'calc' => 'sum' ,'desc' => 'Saves'],
'shotsAllowed' => ['label' => 'SHOTS ALLOWED', 'calc' => 'sum' ,'desc' => 'Shots Allowed'],
			]
		];
	}

	private function team_config() {
		return  [
			'col' => ['values'],
			'pg' => ['g','shg','ppg','pencnt','pim'],
			'fields' => [
'values' => ['label' => 'Values (Must be a Column)', 'calc' => 'none ' ,'desc' => 'Represents all the values to help with ordering columns'],
'date' => ['label' => 'DATE', 'calc' => 'count_distinct' ,'desc' => 'The date of the game'],
'year' => ['label' => 'YEAR', 'calc' => 'count_distinct' ,'desc' => 'The calendar year of the game.'],
'month' => ['label' => 'MONTH', 'calc' => 'count_distinct' ,'desc' => 'The month of the game in YYYY-MM format'],
'day' => ['label' => 'DAY', 'calc' => 'count_distinct' ,'desc' => 'The day of the week of the game'],
'week' => ['label' => 'WEEK', 'calc' => 'count_distinct' ,'desc' => 'The week of the game since season started'],
'type' => ['label' => 'GAME TYPE', 'calc' => 'count_distinct' ,'desc' => 'The type of game EX, NC or Conference Code'],
'shortname' => ['label' => 'Team', 'calc' => 'count_distinct' ,'desc' => 'The name of the team for the player detail'],
'opp_shortname' => ['label' => 'Opponent', 'calc' => 'count_distinct' ,'desc' => 'The opponent of the Team'],
'game_res' => ['label' => 'Team Rec', 'calc' => 'function' ,'desc' => 'Wether the game Resulted in a W L or T. If used as a Value it formats as W-L-T '],
'confcode' => ['label' => 'TEAM CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the players team'],
'opp_confcode' => ['label' => 'OPP CONF', 'calc' => 'count_distinct ' ,'desc' => 'Conference of the opponent'],
'loc' => ['label' => 'Location', 'calc' => 'count_distinct ' ,'desc' => 'Whether the game is Home or Road or Neutral'],
'player_name' => ['label' => 'PLAYER', 'calc' => 'count_distinct ' ,'desc' => 'The Player'],
'yr' => ['label' => 'YR', 'calc' => 'count_distinct ' ,'desc' => 'The Class of the Athlete Fr, So, Jr, Sr and Gr'],
'pos' => ['label' => 'Pos', 'calc' => 'count_distinct ' ,'desc' => 'Whether the player is a forward (F) of a Defenseman (D)'],
'gp' => ['label' => 'GP', 'calc' => 'count_distinct' ,'desc' => 'Games played', 'count_field' => 'game_ID'],
'g' => ['label' => 'G', 'calc' => 'sum' ,'desc' => 'Goals Scored by Player'],
'ppg' => ['label' => 'PPG', 'calc' => 'sum' ,'desc' => 'Power Play Goal'],
'gwg' => ['label' => 'GWG', 'calc' => 'sum' ,'desc' => 'Game Winning Goal'],
'shg' => ['label' => 'SHG', 'calc' => 'sum' ,'desc' => 'Shorthanded Goal'],
'eng' => ['label' => 'ENG', 'calc' => 'sum' ,'desc' => 'Empty Net Goal'],
'eag' => ['label' => 'EAG', 'calc' => 'sum' ,'desc' => 'Extra Attacker Goal'],
'pim' => ['label' => 'PEN MIN', 'calc' => 'sum' ,'desc' => 'Penalty Minutes'],
'pencnt' => ['label' => 'PEN CNT', 'calc' => 'sum' ,'desc' => 'Number of Penalties'],
'period' => ['label' => 'PERIOD', 'calc' => 'sum' ,'desc' => 'Period', 'count_field' => 'game_ID'],
'game_time_secs' => ['label' => 'GAME TIME SECS', 'calc' => 'none' ,'desc' => 'Number of seconds into game of event'],
'time' => ['label' => 'TIME', 'calc' => 'none' ,'desc' => 'Time in period of event in MM:SS'],
'goal_time_class' => ['label' => 'GAME TIME CLASS', 'calc' => 'count_distinct' ,'desc' => 'Breaks period into 3 segments. First 2 Minutes, Middle Period and Last 2 Minutes'],
'infraction' => ['label' => 'BLOCKS', 'calc' => 'count_distinct' ,'desc' => 'Type of Penalty'],
			]
                ];
	}

	public function savepivot () {

		$token= request()->bearerToken();
		$status = "failed";
		$piv = [];

		if ($token == "38|A3AmbzPYyJAaXJDzGpVqe77qPtn06sWtstM8lK2q") {
			$data = request()->all();
			$con = $this->_init_db();

			$json = json_encode($data['config_json']);
			$res = $this->loadpivot($con,$data['serialtoken']);

			if(mysqli_num_rows($res) == 0) {
				$insert = "INSERT INTO userdata.pivot_session (serialtoken,config_json,title,dataset) VALUES ('".$data['serialtoken']."','".$json."','".$data['title']."','".$data['dataset']."')";
                        	#echo $insert;
				mysqli_query($con,$insert);
				$res = $this->loadpivot($con,$data['serialtoken']);
			}

			$piv = mysqli_fetch_object($res);

                        $piv->config_json = json_decode($piv->config_json);
			$piv->config_json->title = $piv->title;
			$piv->config_json->id = $piv->id;
			$piv->config_json->dataset = $piv->dataset;
			$piv->config_json->serialtoken = $piv->serialtoken;

			$status = "success";
		}
		return ['status' => $status, 'rec' => $piv ];
	}

	private function loadpivot ($con,$serialtoken) {
		$select = "SELECT * FROM userdata.pivot_session WHERE serialtoken = '".$serialtoken."'";
		return mysqli_query($con,$select);
	}

	private function loadglobal ($con,$dataset) {

		$select = "SELECT * FROM userdata.pivot_session WHERE dataset = '".$dataset."' AND global = 1";
                $resa = mysqli_query($con,$select);

                $global = [];
                if (mysqli_num_rows($resa)>0) {
			while ($piv = mysqli_fetch_object($resa)) {
				$piv->config_json = json_decode($piv->config_json);
				$piv->config_json->title = $piv->title;
				$piv->config_json->id = $piv->id;
				$piv->config_json->dataset = $piv->dataset;
				$piv->config_json->serialtoken = $piv->serialtoken;
				array_push($global,$piv);
                        }
		}

		return $global;
        }

	public function getpivot ($serialtoken) {
		$con = $this->_init_db();
		$res = $this->loadpivot($con,$serialtoken);
		$piv = mysqli_fetch_object($res);

		$piv->config_json = json_decode($piv->config_json);
		$piv->config_json->title = $piv->title;
		$piv->config_json->id = $piv->id;
		$piv->config_json->dataset = $piv->dataset;
		$piv->config_json->serialtoken = $piv->serialtoken;
		return Response::json($piv);
        }
}
