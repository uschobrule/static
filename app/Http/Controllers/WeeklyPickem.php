<?php

namespace App\Http\Controllers;
use Response;

class WeeklyPickem extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
			
		$template = $parts[0];
		$group_id = "";
		$conf_code = "";
		$gender = "";
		$division = "";
		$week = "";
		$user_id = "";

		if ($count > 1) {
                	$p2 = explode("_",$parts[1]);
			$count2 =  count($p2);

			if ($p2[0] == "u") {
				$user_id = $p2[1];
			}  else {
				if ($count2 > 1) {$group_id = $p2[1];}
                        	if ($count2 > 2) {$conf_code = $p2[2];}
                        	if ($count2 > 3) {$division = $p2[3];}
                        	if ($count2 > 4) {$gender = $p2[4];}
				if ($count2 > 5) {$week = $p2[5];}
			}
		}

		$season = $this->get_season("");
		return array($template,$group_id,$conf_code,$gender,$division,$season,$week,$user_id);
	}

	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($template,$group_id,$conf_code,$gender,$division,$season,$week,$user_id) = $this->parseuri($uri);
		list ($data) = $this->$template($group_id,$conf_code,$gender,$division,$season,$week,$user_id);
	
		$html = view("json/$template", ['data' => $data,'user_id' => $user_id])->render();
	
		return Response::json(array('html' => $html));
	}

	public function leaders($group_id,$conf_code,$gender,$division,$season,$week,$user_id){
		$data = [];

		$ldata = file_get_contents(config('services.JSON_DIR')."pickem/rank-$season.json");
                $data = json_decode($ldata,true);

		return array($data["p10_I_m"]);
	}

	public function group_user($group_id,$conf_code,$gender,$division,$season,$week,$user_id){
	
		$data = [];
		$data['res'] = [];

		$con = $this->_init_db();

		$sql = "";

		if ($conf_code == "p10") {
                $sql= "
SELECT DISTINCT
wp.user_id,u.user_login, u.user_nicename, u.display_name,
g.*,
if(g.hscore != '', g.hscore, lss.hscore) home_score,
if(g.vscore != '', g.vscore, lss.vscore) vis_score,
lss.pd,
lss.pd_time,
wpicks.*,
if(wpicks.win_team_code IS NULL,'None',wpicks.win_team_code) win_team_code_pick,
wpp.week,
chsh.chs_team_code chs_home,
chsv.chs_team_code chs_visitor,
concat(wp.conf,'_',wp.division,'_',wp.gender) conf_code,
CASE WHEN wp.gender = 'm'
THEN concat('Random Pick D',wp.division,' Men\'s')
ELSE concat('Random Pick D',wp.division,' Women\'s')
END conf_name,
CONCAT(SUBSTRING(wpp.start_date,1,4),'-',SUBSTRING(wpp.start_date,5,2),'-',SUBSTRING(wpp.start_date,7,2)) s_date,
CONCAT(SUBSTRING(wpp.end_date,1,4),'-',SUBSTRING(wpp.end_date,5,2),'-',SUBSTRING(wpp.end_date,7,2)) e_date,
DATE_FORMAT(CONCAT(SUBSTRING(g.gdate,1,4),'-',SUBSTRING(g.gdate,5,2),'-',SUBSTRING(g.gdate,7,2)),'%Y-%m-%d') game_date,
CASE
WHEN starttime = '' THEN '17:00:00'
WHEN starttime like '11:% ET' THEN '10:00:00'
WHEN starttime like '12:% ET' THEN '11:00:00'
WHEN starttime like '1:% ET' THEN '12:00:00'
WHEN starttime like '2:% ET' THEN '13:00:00'
WHEN starttime like '3:% ET' THEN '14:00:00'
WHEN starttime like '4:% ET' THEN '15:00:00'
WHEN starttime like '5:% ET' THEN '16:00:00'
WHEN starttime like '6:% ET' THEN '17:00:00'
WHEN starttime like '7:% ET' THEN '18:00:00'
WHEN starttime like '8:% ET' THEN '19:00:00'

WHEN starttime like '11:% AT' THEN '10:00:00'
WHEN starttime like '12:% AT' THEN '11:00:00'
WHEN starttime like '1:% AT' THEN '12:00:00'
WHEN starttime like '2:% AT' THEN '13:00:00'
WHEN starttime like '3:% AT' THEN '14:00:00'
WHEN starttime like '4:% AT' THEN '15:00:00'
WHEN starttime like '5:% AT' THEN '16:00:00'
WHEN starttime like '6:% AT' THEN '17:00:00'
WHEN starttime like '7:% AT' THEN '18:00:00'
WHEN starttime like '8:% AT' THEN '19:00:00'

WHEN starttime like '11:% CT' THEN '11:00:00'
WHEN starttime like '12:% CT' THEN '12:00:00'
WHEN starttime like '1:% CT' THEN '13:00:00'
WHEN starttime like '2:% CT' THEN '14:00:00'
WHEN starttime like '3:% CT' THEN '15:00:00'
WHEN starttime like '4:% CT' THEN '16:00:00'
WHEN starttime like '5:% CT' THEN '17:00:00'
WHEN starttime like '6:% CT' THEN '18:00:00'
WHEN starttime like '7:% CT' THEN '19:00:00'
WHEN starttime like '8:% CT' THEN '20:00:00'

WHEN starttime like '11:% MT' THEN '12:00:00'
WHEN starttime like '12:% MT' THEN '13:00:00'
WHEN starttime like '1:% MT' THEN '14:00:00'
WHEN starttime like '2:% MT' THEN '15:00:00'
WHEN starttime like '3:% MT' THEN '16:00:00'
WHEN starttime like '4:% MT' THEN '17:00:00'
WHEN starttime like '5:% MT' THEN '18:00:00'
WHEN starttime like '6:% MT' THEN '19:00:00'
WHEN starttime like '7:% MT' THEN '20:00:00'
WHEN starttime like '8:% MT' THEN '21:00:00'

WHEN starttime like '3:% GMT' THEN '23:00:00'
WHEN starttime like '7:% GMT' THEN '01:00:00'
WHEN starttime like '8:% GMT' THEN '02:00:00'

ELSE starttime
END revised_start
FROM userdata.game_group gr
JOIN userdata.group_member gmu ON gmu.group_id = gr.group_id
JOIN userdata.weekly_pickem wp ON gmu.user_id = wp.user_id
JOIN userdata.weekly_pickem_period wpp ON wp.division = wpp.division AND wp.gender = wpp.gender
JOIN userdata.weekly_random_game wrg ON wp.gender = wrg.gender AND wp.division = wrg.division
JOIN gd$season.mgames g ON (wrg.game_id = g.rowid AND g.type != 'EX')
LEFT JOIN userdata.weekly_picks wpicks ON wpicks.weekly_pickem_id = wp.weekly_pickem_id AND g.rowid = wpicks.game_id
JOIN db_perpetual.arenas ar ON ar.ID = g.arena_id
LEFT JOIN db_perpetual.".$gender."schoolYxY sh ON sh.code = g.home AND sh.start_year <= $season AND sh.end_year >= $season
LEFT JOIN db_perpetual.".$gender."schoolYxY sv ON sv.code = g.visitor AND sv.start_year <= $season AND sv.end_year >= $season
LEFT JOIN db_perpetual.".$gender."conf shc ON shc.ID = sh.conf_ID
LEFT JOIN db_perpetual.".$gender."conf svc ON svc.ID = sv.conf_ID
LEFT JOIN uschocontent.u_".$gender."confxref AS ch ON ch.code = shc.code
LEFT JOIN uschocontent.u_".$gender."confxref AS cv ON cv.code = svc.code
LEFT JOIN db_perpetual.chs_".$gender."team_map AS chsh ON chsh.team_code = g.home
LEFT JOIN db_perpetual.chs_".$gender."team_map AS chsv ON chsv.team_code = g.visitor
LEFT JOIN uschocontent.u_".$gender."confxref AS gc ON gc.code = g.type
LEFT JOIN livestats.ls_score_summary lss ON lss.gameid = g.rowid
LEFT JOIN content.tourn_type AS tt ON  tt.ID = g.ttype
JOIN uschosocial.wp_users u ON u.ID = wp.user_id
WHERE wpp.season = '$season'
AND wp.gender = '$gender'
AND wpp.week = '$week'
AND wp.conf = '$conf_code'
AND gr.group_id = '$group_id'
AND g.gdate >= wpp.start_date
AND g.gdate <= wpp.end_date
AND g.gdate >= DATE_FORMAT(gr.start_date,\"%Y%m%d\")
AND wp.status = 'active'
ORDER BY g.gdate, revised_start, wpicks.game_id
";
		} else {
		$sql= "
SELECT STRAIGHT_JOIN DISTINCT
wp.user_id,u.user_login, u.user_nicename, u.display_name,
g.*,
if(g.hscore != '', g.hscore, lss.hscore) home_score,
if(g.vscore != '', g.vscore, lss.vscore) vis_score,
lss.pd,
lss.pd_time,
wpicks.*,
if(wpicks.win_team_code IS NULL,'None',wpicks.win_team_code) win_team_code_pick,
wpp.week,
chsh.chs_team_code chs_home,
chsv.chs_team_code chs_visitor,
concat(wp.conf,'_',wp.division,'_',wp.gender) conf_code,
CASE
WHEN wp.gender = 'm' THEN concat(c.name,' D',wp.division,' Men\'s')
ELSE concat(c.name,' D',wp.division,' Women\'s')
END conf_name,
CONCAT(SUBSTRING(wpp.start_date,1,4),'-',SUBSTRING(wpp.start_date,5,2),'-',SUBSTRING(wpp.start_date,7,2)) s_date,
CONCAT(SUBSTRING(wpp.end_date,1,4),'-',SUBSTRING(wpp.end_date,5,2),'-',SUBSTRING(wpp.end_date,7,2)) e_date,
DATE_FORMAT(CONCAT(SUBSTRING(g.gdate,1,4),'-',SUBSTRING(g.gdate,5,2),'-',SUBSTRING(g.gdate,7,2)),'%Y-%m-%d') game_date,
CASE
WHEN starttime = '' THEN '17:00:00'
WHEN starttime like '11:% ET' THEN '10:00:00'
WHEN starttime like '12:% ET' THEN '11:00:00'
WHEN starttime like '1:% ET' THEN '12:00:00'
WHEN starttime like '2:% ET' THEN '13:00:00'
WHEN starttime like '3:% ET' THEN '14:00:00'
WHEN starttime like '4:% ET' THEN '15:00:00'
WHEN starttime like '5:% ET' THEN '16:00:00'
WHEN starttime like '6:% ET' THEN '17:00:00'
WHEN starttime like '7:% ET' THEN '18:00:00'
WHEN starttime like '8:% ET' THEN '19:00:00'

WHEN starttime like '11:% AT' THEN '10:00:00'
WHEN starttime like '12:% AT' THEN '11:00:00'
WHEN starttime like '1:% AT' THEN '12:00:00'
WHEN starttime like '2:% AT' THEN '13:00:00'
WHEN starttime like '3:% AT' THEN '14:00:00'
WHEN starttime like '4:% AT' THEN '15:00:00'
WHEN starttime like '5:% AT' THEN '16:00:00'
WHEN starttime like '6:% AT' THEN '17:00:00'
WHEN starttime like '7:% AT' THEN '18:00:00'
WHEN starttime like '8:% AT' THEN '19:00:00'

WHEN starttime like '11:% CT' THEN '11:00:00'
WHEN starttime like '12:% CT' THEN '12:00:00'
WHEN starttime like '1:% CT' THEN '13:00:00'
WHEN starttime like '2:% CT' THEN '14:00:00'
WHEN starttime like '3:% CT' THEN '15:00:00'
WHEN starttime like '4:% CT' THEN '16:00:00'
WHEN starttime like '5:% CT' THEN '17:00:00'
WHEN starttime like '6:% CT' THEN '18:00:00'
WHEN starttime like '7:% CT' THEN '19:00:00'
WHEN starttime like '8:% CT' THEN '20:00:00'

WHEN starttime like '11:% MT' THEN '12:00:00'
WHEN starttime like '12:% MT' THEN '13:00:00'
WHEN starttime like '1:% MT' THEN '14:00:00'
WHEN starttime like '2:% MT' THEN '15:00:00'
WHEN starttime like '3:% MT' THEN '16:00:00'
WHEN starttime like '4:% MT' THEN '17:00:00'
WHEN starttime like '5:% MT' THEN '18:00:00'
WHEN starttime like '6:% MT' THEN '19:00:00'
WHEN starttime like '7:% MT' THEN '20:00:00'
WHEN starttime like '8:% MT' THEN '21:00:00'

WHEN starttime like '3:% GMT' THEN '23:00:00'
WHEN starttime like '7:% GMT' THEN '01:00:00'
WHEN starttime like '8:% GMT' THEN '02:00:00'

ELSE starttime
END revised_start
FROM userdata.game_group gr
JOIN userdata.group_member gmu ON gmu.group_id = gr.group_id
JOIN userdata.weekly_pickem wp ON gmu.user_id = wp.user_id
JOIN userdata.weekly_pickem_period wpp ON wp.division = wpp.division AND wp.gender = wpp.gender
JOIN db_perpetual.".$gender."conf c ON c.code = wp.conf
JOIN db_perpetual.".$gender."schoolYxY s ON c.ID = s.conf_ID AND s.division = wp.division AND s.start_year <= wpp.season AND s.end_year >= wpp.season
JOIN gd$season.".$gender."games g ON ((g.home = s.code OR g.visitor = s.code) AND g.type != 'EX')
LEFT JOIN userdata.weekly_picks wpicks ON wpicks.weekly_pickem_id = wp.weekly_pickem_id AND g.rowid = wpicks.game_id
JOIN db_perpetual.arenas ar ON ar.ID = g.arena_id
LEFT JOIN db_perpetual.".$gender."schoolYxY sh ON sh.code = g.home AND sh.start_year <= $season AND sh.end_year >= $season
LEFT JOIN db_perpetual.".$gender."schoolYxY sv ON sv.code = g.visitor AND sv.start_year <= $season AND sv.end_year >= $season
LEFT JOIN db_perpetual.".$gender."conf shc ON shc.ID = sh.conf_ID
LEFT JOIN db_perpetual.".$gender."conf svc ON svc.ID = sv.conf_ID
LEFT JOIN uschocontent.u_".$gender."confxref AS ch ON ch.code = shc.code
LEFT JOIN uschocontent.u_".$gender."confxref AS cv ON cv.code = svc.code
LEFT JOIN db_perpetual.chs_".$gender."team_map AS chsh ON chsh.team_code = g.home
LEFT JOIN db_perpetual.chs_".$gender."team_map AS chsv ON chsv.team_code = g.visitor
LEFT JOIN uschocontent.u_".$gender."confxref AS gc ON gc.code = g.type
LEFT JOIN livestats.ls_score_summary lss ON lss.gameid = g.rowid
LEFT JOIN content.tourn_type AS tt ON  tt.ID = g.ttype
JOIN uschosocial.wp_users u ON u.ID = wp.user_id
WHERE wpp.season = '$season'
AND wp.gender = '$gender'
AND wpp.week = '$week'
AND wp.conf = '$conf_code'
AND gr.group_id = '$group_id'
AND g.gdate >= wpp.start_date
AND g.gdate <= wpp.end_date
AND g.gdate >= DATE_FORMAT(gr.start_date,\"%Y%m%d\")
AND wp.status = 'active'
ORDER BY g.gdate, revised_start, wpicks.game_id
";
		}

//echo $sql;

                date_default_timezone_set('US/Eastern');
                $cur_time =  date('Y-m-d H:i:s');

                $group_user_result=mysqli_query($con,$sql);

                if(mysqli_num_rows($group_user_result)>0)  {
			$ind['c'] = 0;
			
                        while ($d = mysqli_fetch_object($group_user_result)) {
				if (!array_key_exists($d->rowid,$ind)) {
					$ind[$d->rowid] = $ind['c'];
					$ind['c']++;
				}

				$start_cutoff = $d->game_date." ".$d->revised_start;

				$d->row_class = "wp_empty";

				//echo "$start_cutoff > $cur_time";
				if ($start_cutoff > $cur_time) {
					$d->win_team_code_pick = "X";
				} elseif ($d->home_score != "") {
					if (!array_key_exists($d->user_login,$data['res'])) {
						$data['res'][$d->user_login] = [];
						$data['res'][$d->user_login]['cnt'] = 0;
						$data['res'][$d->user_login]['cur'] = 0;
					}

					$data['res'][$d->user_login]['cnt']++;
					if ($d->home_score > $d->vis_score && $d->win_team_code_pick == $d->home) {
						$d->row_class = "wp_win";
						$data['res'][$d->user_login]['cur']++;
					} elseif ($d->home_score < $d->vis_score && $d->win_team_code_pick == $d->visitor) {
						$d->row_class = "wp_win";
						$data['res'][$d->user_login]['cur']++;
					} elseif ($d->home_score == $d->vis_score) {
						$d->row_class = "wp_tie";
					} else {
						$d->row_class = "wp_loss";
					}
				}

				$data['u'][$d->user_login][$ind[$d->rowid]] = $d;
				$data['g'][$ind[$d->rowid]] = $d;
				$data['cn'] =  $d->conf_name ." Week ".$d->week." ".$d->s_date." to ".$d->e_date;
                        }

                }
	//	var_dump($data);

		
                //var_dump($data['res']);

		$sdata = $this->SortByKeyValue($data['res'],'cur',1);
	//	var_dump($sdata);

		$prnk = 0;
		$cur = 1000000;
		$rnk = 1;
		$data['rnk'] = [];

		foreach ($sdata AS $ind => $rec) {
			foreach ($rec AS $u => $uv) {
				$data['rnk'][$rnk] = [];
				if ($uv['cur']< $cur) {
					$prnk = $rnk;
					$cur = $uv['cur'];
				}
				$uv['rnk'] = $prnk;
				$uv['username'] = $u;
				$data['rnk'][$rnk] = $uv;
				$rnk++;
			}
		}

//		var_dump($data['rnk']);

		return array($data);
	}
}
