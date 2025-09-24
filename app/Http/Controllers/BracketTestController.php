<?php

namespace App\Http\Controllers;
use Response;
use Request;

class BracketTestController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$key2 = "";

                $template = $parts[0];
                $gender = $this->get_gender($parts[1]);
                $division = $parts[2];
		$season = $parts[3];
		$key  = $parts[4];
		if ($count > 5) {
			$p2 = explode("_",$parts[5]);
                        $count2 =  count($p2);

                        if ($p2[0] == "u") {
                                $key2 = $p2[1];
			}
		}

                return array($template,$gender,$division,$season,$key,$key2);
	}
	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($template,$gender,$division,$season,$key,$key2) = $this->parseuri($uri);

		if(method_exists($this, $template)){
			list($data,$meta_data,$datatable,$html) = $this->$template($gender,$division,$season,$key,$key2);
			return Response::json(array('html' => $html, 'json' => $data, 'meta_data' => $meta_data, 'datatable' => $datatable));
		}

                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => '', 'datatable' => []));              
	}

	public function pwpseeds($gender,$division,$season,$user_id) {
		$filename = $this->get_jsonfilename("scoreboard","tourn_schedule",$gender,$division,$season,"");
		if (file_exists($filename)) {

			$seeds_string = file_get_contents($filename);
                        $seeds['games'] = json_decode($seeds_string);
	
			$struct_string = file_get_contents(config('services.JSON_DIR')."site/tourn_structure.json");
			$struct = json_decode($struct_string);

			foreach ($seeds['games'] as $conf => $rec) {
				$type = $rec->type;
				$structure = $struct->$type;
				$rec->team_name = [];
				$cgm = [];

				foreach ($rec->teams as $ind => $code) {
					$tdata = $this->team_map($gender,$code);
					$rec->team_name[$ind] = $tdata->shortname;
				}

				foreach ($structure as $gt => $gm) {
					$h = $gm->h;
					$v = $gm->v;
					if ($h > 0) {
						$cgm[$gt]['h'] = $h;
						$cgm[$gt]['hcode'] = $rec->teams->$h;
						$cgm[$gt]['hname'] = $rec->team_name[$h];
						$cgm[$gt]['v'] = $v;
						$cgm[$gt]['vcode'] = $rec->teams->$v;
                                        	$cgm[$gt]['vname'] = $rec->team_name[$v];
						$cgm[$gt]['div'] = $gm->div;
					}
				}

				$rec->gm = $cgm;

				#var_dump($rec);
			}

			$con = $this->_init_db();
			
                	$sqlgt = "SELECT IF(gt.cutoff_date < now(), 1,0) past_cutoff,
DATE_FORMAT(gt.cutoff_date,'%M %e, %I:%i %p') display_cutoff_date
FROM userdata.game_type gt WHERE game_type_id = 2";

			//echo $sqlgt;

                	$gt=mysqli_query($con,$sqlgt);
			$dgt= mysqli_fetch_object($gt);

			$seeds['init']['display_cutoff_date'] = $dgt->display_cutoff_date;
			$seeds['init']['past_cutoff'] = $dgt->past_cutoff;
			$seeds['init']['config'] = [];
			$seeds['init']['user_pts'] = 0;
			$seeds['init']['tiebreaker'] = "";
			$seeds['init']['tiebreaker2'] = "";
			$seeds['init']['ctiebreaker'] = 0;
			$seeds['init']['ctiebreaker2'] = 0;

                        $sql = "
SELECT u.user_login,ub.user_id, SUM(ubd.pts) pts, ub.config_storage, ub.tiebreaker, ub.tiebreaker2
FROM userdata.user_bracket ub
JOIN userdata.user_bracket_detail ubd ON ub.user_bracket_id = ubd.user_bracket_id
JOIN uschosocial.wp_users u ON u.ID = ub.user_id
WHERE ub.user_id = $user_id
AND ub.season =  '$season'
AND ub.game_type_id = 2
GROUP BY ub.user_id";

//              echo $sql;

                        $picks=mysqli_query($con,$sql);

                	if(mysqli_num_rows($picks)>0) {
                        	$d = mysqli_fetch_object($picks);
				$seeds['init']['config'] = json_decode($d->config_storage);
				$seeds['init']['user_pts'] = $d->pts;
				$seeds['init']['tiebreaker'] = $d->tiebreaker;
				$seeds['init']['tiebreaker2'] = $d->tiebreaker2;
			}

	                $filename = config('services.JSON_DIR')."/bracketcontest/gt2-".$season.".json";
       			// echo $filename;
                	if (file_exists($filename)) {
                        	$gt = file_get_contents($filename,true);
                        	$dt = json_decode($gt,TRUE);
	
				$seeds['init']['res'] = $dt['games'];
				$seeds['init']['ctiebreaker'] = $dt['res']['tiebreaker'];
				$seeds['init']['ctiebreaker2'] = $dt['res']['tiebreaker2'];
			}

                        # list ($data) = $this->$templatec($template,$json_obj,$gender,$division);
                        return array($seeds,'','','');
		}
	}

	public function gconfig($gender,$division,$season,$game_type_id,$user_id) {
		$resp = ['type' => 'gconfig', 'user_grps' => [], 'user_manage_grps' => [], 'active_grps' => []];

		$con = $this->_init_db();

		$ugrp_sql = "select gg.group_id, CONCAT(group_name,IF(gg.is_open,' (open)',' (private)')) AS group_name  from userdata.game_group gg JOIN userdata.group_member ggm ON gg.group_id = ggm.group_id where game_type_id = ".$game_type_id." and season = ".$season." AND user_id = ".$user_id." and gg.active_ind = 1 and ggm.member_status = 'active' ORDER BY group_name";
		$ugdata=mysqli_query($con,$ugrp_sql);

		while ($row = mysqli_fetch_assoc($ugdata)) {
			array_push($resp['user_grps'],$row);
		}

		$ucgrp_sql = "select * from userdata.game_group where game_type_id = ".$game_type_id." and season = ".$season." AND create_user_id = ".$user_id." ORDER BY group_name";
                $ucgdata=mysqli_query($con,$ucgrp_sql);

                while ($row = mysqli_fetch_assoc($ucgdata)) {
                        array_push($resp['user_manage_grps'],$row);
		}

		$agrp_sql = "select group_id AS id, group_id, CONCAT(group_name,IF(is_open,' (open)',' (private)')) AS text, group_name, is_open,passwd,active_ind from userdata.game_group where game_type_id = ".$game_type_id." and season = ".$season." AND active_ind = 1 ORDER BY group_name";
                $agdata=mysqli_query($con,$agrp_sql);

                while ($row = mysqli_fetch_assoc($agdata)) {
                        array_push($resp['active_grps'],$row);
                }

		return $resp;
	}

	public function gresults($gender,$division,$season,$game_type_id,$user_id) {
		$resp = ['type' => 'gresults', 'group' => ['info' => []]];


		$filename = config('services.JSON_DIR')."/bracketcontest/gt5-".$season.".json";

		$ctiebreaker = 0;

                // echo $filename;
                if (file_exists($filename)) {
                	$gt = file_get_contents($filename,true);
			$dt = json_decode($gt,TRUE);
                        if ($dt['res']['tiebreaker'] != '') {
                        	$ctiebreaker = $dt['res']['tiebreaker'];
                        }
		}

		$con = $this->_init_db();

		$resp['past_cutoff'] = $this->test_game_cutoff($con,$game_type_id);

		$ugrp_sql = "select gg.group_id, group_name from userdata.game_group gg JOIN userdata.group_member ggm ON gg.group_id = ggm.group_id where game_type_id = ".$game_type_id." and season = ".$season." AND user_id = ".$user_id." and gg.active_ind = 1 and ggm.member_status = 'active' ORDER BY group_name";
                $ugdata=mysqli_query($con,$ugrp_sql);

		#$season = 20202021;
                $nr = $this->user_nat_results($con,$season,$game_type_id,$ctiebreaker);

		$resp['past_cutoff'] = $this->test_game_cutoff($con,$game_type_id);

                while ($row = mysqli_fetch_assoc($ugdata)) {
			array_push($resp['group']['info'],$row);
			$resp['group']['results'][$row['group_id']] = $this->user_group_results($con,$season,$row['group_id'],$game_type_id,$nr,$resp['past_cutoff'],$ctiebreaker);
		}
		
                return $resp;
	}

        private function user_group_results($con,$season,$group_id,$game_type_id,$nr,$past_cutoff,$ctiebreaker){

                $gr = [];

                if ($group_id) {
                        $sql= "SELECT gm.user_id, u.user_login, u.user_nicename, gg.group_name, sum(ubd.pts) pts, ub.tiebreaker,ABS(ub.tiebreaker - ".$ctiebreaker.") AS ptiebreaker,MAX(if(ubd.game_code = 'ncaaf',ubd.pick,'')) champion 
FROM userdata.game_group gg 
JOIN userdata.group_member gm ON gg.group_id = gm.group_id AND gm.member_status = 'active'
JOIN uschosocial.wp_users u ON u.ID = gm.user_id 
JOIN userdata.user_bracket ub ON ub.user_id = gm.user_id 
JOIN userdata.user_bracket_detail ubd ON ubd.user_bracket_id = ub.user_bracket_id 
WHERE gg.group_id = $group_id 
AND ub.season = '$season'
AND ub.game_type_id = $game_type_id
GROUP BY ub.user_bracket_id 
ORDER BY pts DESC, ptiebreaker ASC";

              	//	echo $sql;

                        $prnk =0;
                        $cur_pts = 1000;
                        $rk = 0;

                        $group_data_result=mysqli_query($con,$sql);

                        if(mysqli_num_rows($group_data_result)>0)  {
                                while ($gd = mysqli_fetch_object($group_data_result)) {
                                        $rk++;

                                        if ($gd->pts < $cur_pts) {
                                                $cur_pts = $gd->pts;
                                                $prnk = $rk;
                                        }

                                        $gd->grp_rnk = $prnk;
					$gd->nat_rnk = $nr[$gd->user_id]['nat_rnk'];
					if ($past_cutoff == 0) {$gd->champion  = "";$gd->tiebreaker = 0;}
					array_push($gr,$gd);	
				}
                        }
                }

                return $gr;
        }

	private function user_nat_results($con,$season,$game_type_id,$ctiebreaker) {
		$nr = [];

		$sqln= "SELECT ub.user_id, u.user_nicename, sum(ubd.pts) pts,ABS(ub.tiebreaker - ".$ctiebreaker.") AS ptiebreaker
FROM userdata.user_bracket ub
JOIN uschosocial.wp_users u ON u.ID = ub.user_id
JOIN userdata.user_bracket_detail ubd ON ubd.user_bracket_id = ub.user_bracket_id
WHERE ub.season = '$season'
AND ub.game_type_id = $game_type_id
GROUP BY ub.user_bracket_id
ORDER BY pts DESC, ptiebreaker ASC";

		//echo $sqln;

                $prnk =0;
                $cur_pts = 1000;
                $rk = 0;

                $nat_result=mysqli_query($con,$sqln);

                if(mysqli_num_rows($nat_result)>0) {
                        while ($gd = mysqli_fetch_object($nat_result)) {
                                $rk++;

                                if ($gd->pts < $cur_pts) {
                                        $cur_pts = $gd->pts;
                                        $prnk = $rk;
				}
				if (!array_key_exists($gd->user_id,$nr)){
					$nr[$gd->user_id]['nat_rnk'] = [];
				}
                                $nr[$gd->user_id]['nat_rnk'] = $prnk;
                        }
		}

		return $nr;

	}

        public function ncaaseeds($gender,$division,$season,$game_type_id,$user_id) {
                $filename = $this->get_jsonfilename("scoreboard","tourney_bracket",$gender,$division,$season,"");
#		echo $filename;
		if (file_exists($filename)) {
			$resp = ['type' => 'ncaaseeds'];
                        $seeds_string = file_get_contents($filename);
                        $seeds = json_decode($seeds_string, TRUE);

	                $struct_string = file_get_contents(config('services.JSON_DIR')."site/tourn_structure.json");
                        $struct = json_decode($struct_string);
				$structure = $struct->r4;

				$resp['struct']['FF'] = $seeds['struct']['FF'];

				foreach ($seeds['teams'] as $reg => $rec) {
					$tm[$reg] = [];
					$cgm = [];

					foreach ($rec as $ind => $code) {
						$tdata = $this->team_map($gender,$code['name']);
						$tm[$reg][$code['seed']]['tm'] = $tdata;
					$tm[$reg][$code['seed']]['code'] = $code;
					$lreg = $code['reg'];
					$resp['struct'][$reg] = $seeds['struct'][$lreg];
                                }

                                foreach ($structure as $gt => $gm) {
                                        $h = $gm->h;
                                        $v = $gm->v;
#				echo "$reg $gt $h $v<br>";
                                        if ($h > 0) {
                                                $cgm[$gt]['h'] = $h;
						$cgm[$gt]['hoseed'] = $tm[$reg][$h]['code']['oseed'];
                                                $cgm[$gt]['hcode'] = $tm[$reg][$h]['tm']->code;
                                                $cgm[$gt]['hname'] = $h.$tm[$reg][$h]['code']['reg']." ".$tm[$reg][$h]['tm']->shortname;
                                                $cgm[$gt]['v'] = $v;
#						var_dump( $tm[$reg][$v]);
#						var_dump( $tm[$reg][$v]['code']);
						$cgm[$gt]['voseed'] = $tm[$reg][$v]['code']['oseed'];
                                                $cgm[$gt]['vcode'] = $tm[$reg][$v]['tm']->code;
                                                $cgm[$gt]['vname'] = $v.$tm[$reg][$v]['code']['reg']." ".$tm[$reg][$v]['tm']->shortname;
                                                $cgm[$gt]['div'] = $gm->div;
                                        }
                                }

                                $resp['gm'][$reg] = $cgm;

                           //     var_dump($rec['gm']);
                        }

                        $con = $this->_init_db();

			$sqlgt = "SELECT IF(gt.cutoff_date < DATE_SUB(now(),INTERVAL 4 HOUR), 1,0) past_cutoff,
				 DATE_SUB(now(),INTERVAL 4 HOUR) cur_time,
DATE_FORMAT(gt.cutoff_date,'%M %e, %I:%i %p') display_cutoff_date
FROM userdata.game_type gt WHERE game_type_id = $game_type_id";

                        //echo $sqlgt;

                        $gt=mysqli_query($con,$sqlgt);
                        $dgt= mysqli_fetch_object($gt);

                        $resp['init']['display_cutoff_date'] = $dgt->display_cutoff_date;
			$resp['init']['past_cutoff'] = $dgt->past_cutoff;
			$resp['init']['cur_time'] = $dgt->cur_time;
			$resp['init']['config'] = [];
                        $resp['init']['user_pts'] = 0;
                        $resp['init']['tiebreaker'] = "";
                        $resp['init']['ctiebreaker'] = 0;

                        $sql = "
SELECT u.user_login,ub.user_id, SUM(ubd.pts) pts, ub.config_storage, ub.tiebreaker, ub.storage_serial pick_storage
FROM userdata.user_bracket ub
JOIN userdata.user_bracket_detail ubd ON ub.user_bracket_id = ubd.user_bracket_id
JOIN uschosocial.wp_users u ON u.ID = ub.user_id
WHERE ub.user_id = $user_id
AND ub.season =  '$season'
AND ub.game_type_id = $game_type_id
GROUP BY ub.user_id";

//              echo $sql;

                        $picks=mysqli_query($con,$sql);

                        if(mysqli_num_rows($picks)>0) {
                                $d = mysqli_fetch_object($picks);
				$pick = json_decode($d->pick_storage);
				$resp['init']['pick_storage'] = $pick;
                                $resp['init']['config'] = json_decode($d->config_storage);
                                $resp['init']['user_pts'] = $d->pts;
                                $resp['init']['tiebreaker'] = $d->tiebreaker;
                        }

                        $filename = config('services.JSON_DIR')."bracketcontest/gt7-".$season.".json";
			$filenameres = config('services.JSON_DIR')."bracketcontest/res-".$season.".json";


                        // echo $filename;
                        if (file_exists($filename)) {
                                $gt = file_get_contents($filename,true);
				$dt = json_decode($gt,TRUE);

				$gamesf = [];
				foreach ($dt['games'] AS $game_code => $res) {
                                	$gamesf[$game_code] = $res['win_code'];
                                }
				
				$resp['init']['gamesf'] = $gamesf;
				$resp['init']['ctiebreaker'] = 0;
				if ($dt['res']['tiebreaker'] != '') {
                                	$resp['init']['ctiebreaker'] = $dt['res']['tiebreaker'];
				}
                        }

			$resp['cur_time_php'] = date('Y-m-d H:i:s');


			return $resp;
		}
	}

	public function submit($gender,$division,$season,$game_type_id,$user_id,$type,$seq,$serial) {

		$test = $this->validate_creds($seq,$serial,$user_id);
		if ($test) {
			$method = "submit_".$type."_".$game_type_id;
			return $this->$method($gender,$division,$season,$game_type_id,$user_id);
		} else {
			return ['status' => 'error', 'message' => 'Validation Failed','type' => 'submit'];
		}
	}

	private function test_game_cutoff($con,$game_type_id) {
		
		$sql = "SELECT IF(gt.cutoff_date < DATE_SUB(now(),INTERVAL 4 HOUR), 1,0) AS past_cutoff FROM userdata.game_type gt WHERE game_type_id = ".$game_type_id;
		$gt=mysqli_query($con,$sql);
                $test = mysqli_fetch_object($gt);

		return $test->past_cutoff;
	}

	private function submit_leave_7($gender,$division,$season,$game_type_id,$user_id) {
                $con = $this->_init_db();

                $data = request()->all();
                $group_id = $data['group_id'];
                $group_name = $data['group_name'];

                $res = ['status' => 'success', 'message' => 'You have left the group '.$group_name.".",'type' => 'submit'];

                if ($group_id) {
                        $update_sql = "UPDATE userdata.group_member SET member_status = 'inactive' WHERE group_id = ".$group_id." AND user_id = ".$user_id.";";
                        //echo $update_sql;
                        mysqli_query($con,$update_sql);
                } else {
                        $res = ['status' => 'error', 'message' => 'ERROR: Attempting to leave group '.$group_name.' with no ID','type' => 'submit'];
                }

                return $res;
	}

	private function submit_reset_7($gender,$division,$season,$game_type_id,$user_id) {
                $con = $this->_init_db();
		
		$res = ['status' => 'success', 'message' => 'You have reset your bracket.', 'type' => 'submit'];

		if($this->test_game_cutoff($con,$game_type_id)) {
                        $res = ['status' => 'error', 'message' => 'Sorry. The cutoff time has passed.','type' => 'submit'];
                } else {

			$sqlbr = "SELECT * FROM userdata.user_bracket WHERE game_type_id = ".$game_type_id." AND season = ".$season." AND user_id = ".$user_id;

			$br=mysqli_query($con,$sqlbr);
                	$brack = mysqli_fetch_object($br);

                	if ($brack) {
				$delete_sql = "DELETE FROM userdata.user_bracket WHERE user_bracket_id = ".$brack->user_bracket_id;
                        	//echo $delete_sql;
                        	$result = mysqli_query($con,$delete_sql);

                        	$delete_sql2 = "DELETE FROM userdata.user_bracket_detail WHERE user_bracket_id = ".$brack->user_bracket_id;
                        	//echo $delete_sql2;
                        	mysqli_query($con,$delete_sql2);
			}
		}

                return $res;
	 }

	 private function submit_create_7($gender,$division,$season,$game_type_id,$user_id) {
                $con = $this->_init_db();

		$data = request()->all();
		$group_name = str_replace("'","\'",$data['group_name']);
		$is_open = $data['is_open'];
		$passwd = $data['passwd'];

                $res = ['status' => 'success', 'message' => 'You have created and joined the group '.$data['group_name'].".",'type' => 'create'];

                if ($group_name) {
			$insert_sql = "INSERT INTO userdata.game_group (game_type_id,create_user_id,season,group_name,is_open,passwd,active_ind,create_date,start_date) VALUES ('".$game_type_id."','".$user_id."','".$season."','".$group_name."','".$is_open."','".$passwd."','1',CURRENT_TIMESTAMP(),'0000-00-00')";
                        #echo $insert_sql;
			$result = mysqli_query($con,$insert_sql);
			$group_id = mysqli_insert_id($con);
			
			$insert_sql2 = "INSERT INTO userdata.group_member (group_id,user_id,member_status) VALUES ('".$group_id."','".$user_id."','active')";
                        //echo $insert_sql2;
                        mysqli_query($con,$insert_sql2);

			$res['gconfig'] = $this->gconfig($gender,$division,$season,$game_type_id,$user_id);
                } else {
                        $res = ['status' => 'error', 'message' => 'ERROR: Attempting to create group failed '. $group_name,'type' => 'submit'];
                }

                return $res;
	}

	private function submit_manage_7($gender,$division,$season,$game_type_id,$user_id) {
                $con = $this->_init_db();

                $data = request()->all();
                $group_id = $data['group_id'];
		$group_name = str_replace("'","\'",$data['group_name']);
		$is_open = $data['is_open'];
		$passwd = $data['passwd'];
		$active_ind = $data['active_ind'];

                $res = ['status' => 'success', 'message' => 'Your changes have been made to the group '.$data['group_name'].".",'type' => 'submit'];

                if ($group_id) {
			$update_sql = "UPDATE userdata.game_group SET active_ind = '".$active_ind."',group_name = '".$group_name."', is_open = '".$is_open."', passwd ='".$passwd."' WHERE group_id = ".$group_id;
                #        echo $update_sql;
                        mysqli_query($con,$update_sql);
                } else {
                        $res = ['status' => 'error', 'message' => 'ERROR: Attempting to manage a group with no ID','type' => 'submit'];
                }

                return $res;
        }


	private function submit_join_7($gender,$division,$season,$game_type_id,$user_id) {
		$con = $this->_init_db();
		
		$data = request()->all();
		$group_id = $data['group_id'];
		$group_name = $data['group_name'];

		$res = ['status' => 'success', 'message' => 'You have joined the group '.$group_name.".",'type' => 'submit'];

		if ($group_id) {
			$sql = "SELECT * FROM userdata.group_member WHERE group_id = ".$group_id." AND user_id = ". $user_id;
                        $test = mysqli_query($con,$sql);

                        if(mysqli_num_rows($test)>0) {
				$update_sql = "UPDATE userdata.group_member SET member_status = 'active' WHERE group_id = ".$group_id." AND user_id = ".$user_id.";";
                		//echo $update_sql;
				mysqli_query($con,$update_sql);
			} else {
				$insert_sql = "INSERT INTO userdata.group_member (group_id,user_id,member_status) VALUES ('".$group_id."','".$user_id."','active')";
                                //echo $insert_sql;
                                mysqli_query($con,$insert_sql);
			}
		} else {
			$res = ['status' => 'error', 'message' => 'ERROR: Attempting to join a group with no ID','type' => 'submit'];
		}

		return $res;
	}

	private function submit_picks_7($gender,$division,$season,$game_type_id,$user_id) {
		# test cutoff date
		$data = request()->all();
		$picks = $data['picks'];
		$picks_string = json_encode($picks);
		$tiebreaker = $data['tiebreaker'];

		$res = ['status' => 'success', 'message' => 'Your picks have been Updated','type' => 'submit'];

		$con = $this->_init_db();
		
		if($this->test_game_cutoff($con,$game_type_id)) {
			$res = ['status' => 'error', 'message' => 'Sorry. The cutoff time has passed.','type' => 'submit'];
		} else {
			$sql = "SELECT * FROM userdata.user_bracket WHERE season = '".$season."' AND game_type_id = ".$game_type_id." AND user_id = ". $user_id;
			$brack_test = mysqli_query($con,$sql);

			$bracket_id = 0;

			if(mysqli_num_rows($brack_test)>0) {
				$brack=mysqli_fetch_object($brack_test);
				$update_sql = "UPDATE userdata.user_bracket SET storage_serial = '".$picks_string."',tiebreaker ='".$tiebreaker."' WHERE user_bracket_id =".$brack->user_bracket_id;
				//echo $update_sql;
				mysqli_query($con,$update_sql);
				$bracket_id = $brack->user_bracket_id;
			} else {
				$insert_sql = "INSERT INTO userdata.user_bracket (game_type_id,user_id,season,storage_serial,tiebreaker) VALUES ('".$game_type_id."','".$user_id."','".$season."','".$picks_string."','".$tiebreaker."')";
				//echo $insert_sql;
				$result=mysqli_query($con,$insert_sql);
				$bracket_id = mysqli_insert_id($con);
			}

			$delete_sql = "DELETE FROM userdata.user_bracket_detail WHERE user_bracket_id = $bracket_id";
			mysqli_query($con,$delete_sql);
			
			foreach ($picks as $gc => $pck) {
				$insert_det_sql = "INSERT INTO userdata.user_bracket_detail (user_bracket_id,game_code,pick) VALUES ('".$bracket_id."','".$gc."','".$pck."')";
                                //echo $insert_det_sql;
                                mysqli_query($con,$insert_det_sql);

			}
		}

		return $res;
	}

	private function validate_creds($seq,$serial,$user_id) {
		$secret = "51c016c0d8bcbd9864525ca821813c72";
		$string = $secret."-".$user_id."-".$seq;

		$test_serial = md5($string);
		if ($serial == $test_serial) {
			return 1;
		} else {
			return 0;
		}
        }

        public function leaders($gender,$division,$season,$game_type_id) {
  		$datatable['dt1']['sub_title'] = '';
               	$meta_data = [];
               	$meta_data["paging"] = 1;
               	$meta_data["bFilter"] = 1;
               	$meta_data["bInfo"] = 0;

               	$meta_data["order"] = [];
               	array_push($meta_data["order"], "0");
               	array_push($meta_data["order"], "asc");

               	$columns = [];
               	$columns[0]["title"] = "Rnk";
               	$columns[1]["title"] = "User";
               	$columns[2]["title"] = "Pts";
	       	$columns[3]["title"] = "Tiebreaker";
		if ($game_type_id == 2) {
	       		$columns[4]["title"] = "Tiebreaker2";
		} else {
			$columns[4]["title"] = "Champion";
		}

               	$meta_data["columns"] = $columns;

               	$dat = [];
               	$ind = 1;
		$pind = 1;
		$ppts = 0;

                $filename = config('services.JSON_DIR')."bracketcontest/gt".$game_type_id."-".$season.".json";
		$lnk = [];
		$lnk[2] = "/pairwise-predictor-bracket-challenge/";
                $lnk[7] = "/ncaa-college-hockey-tourney-bracket/";

		// echo $filename;
                if (file_exists($filename)) {
                        $gt = file_get_contents($filename);
                        $dt = json_decode($gt);

                        $struct_string = file_get_contents(config('services.JSON_DIR')."site/tourn_structure.json");
                        $struct = json_decode($struct_string);

                        foreach ($dt->rank as $d) {
	        		$rec = [];
			
				if ($ppts != $d->pts) {
					$pind = $ind;
					$ppts = $d->pts;
				}
				
       		       	 	$rec[0] = $pind;
				if ($d->past_cutoff == 1) {
                        		$rec[1] = "<a href='".$lnk[$game_type_id]."?u=".$d->user_id."'>".$d->user_login."</a>";
				} else {
					$rec[1] = $d->user_login;
				}
                        	$rec[2] = $d->pts;
				$rec[3] = $d->tiebreaker;
				$rec[4] = $d->tiebreaker2;
			
       		                array_push($dat,$rec);
				$ind++;
			}

                	$data['dt1']['data']['data'] = $dat;
                	$data['dt1']['data']['meta_data'] = $meta_data;

                	$data['dt1']['meta_data'] = $meta_data;
                	$data['dt1']['sub_title'] = "";
		}

                # list ($data) = $this->$templatec($template,$json_obj,$gender,$division);
		return $data['dt1'];
        }

	public function sidebar($gender,$division,$season,$game_type_id,$user_id=0) {
		$data = [];
                $ind = 1;
                $pind = 1;
                $ppts = 0;
	
		$cuser_login  = "";
		$cpts = 0;
		$crnk = 0;
		$ctiebreaker ="";
		$ctiebreaker2 = "";
                
		$filename = "config('services.JSON_DIR')."/bracketcontest/gt".$game_type_id."-".$season.".json";
                // echo $filename;
                $lnk[2] = "/pairwise-predictor-bracket-challenge/";
                $lnk[4] = "/ncaa-college-hockey-tourney-bracket/";

                if (file_exists($filename)) {
                        $gt = file_get_contents($filename);
                        $dt = json_decode($gt);

                        $struct_string = file_get_contents(config('services.JSON_DIR')."site/tourn_structure.json");
                        $struct = json_decode($struct_string);

                        foreach ($dt->rank as $d) {
                                $rec = [];

                                if ($ppts != $d->pts) {
                                        $pind = $ind;
                                        $ppts = $d->pts;
                                }

                                $rec[0] = $pind;

				$tuname = $d->user_login;
				if (strlen($tuname) > 8) {
					$tuname = substr($d->user_login,0,8);
        				$tuname = $tuname."**";
				}

                                if ($d->past_cutoff == 1) {
					$rec[1] = '<a href="'.$lnk[$game_type_id].'?u='.$d->user_id.'">'.$tuname.'</a>';
                                } else {
                                        $rec[1] = $tuname;
                                }
                                $rec[2] = $d->pts;
                                $rec[3] = $d->tiebreaker;
                                $rec[4] = $d->tiebreaker2;

                                array_push($data,$rec);
                                $ind++;

				if ($d->user_id == $user_id) {
                                        $cuser_login = $d->user_login;
					$cpts = $d->pts;
					$ctiebreaker =  $d->tiebreaker;
					if ($game_type_id == 2) {
       		                                 $ctiebreaker2 = $d->tiebreaker2;
	                                }
					$crnk = $pind;
                                }
                        }
                }

		$header[2] = "Predictor";
		$header[4] = "NCAA";
		$leaders[2] = "/predictor-bracket-leaders/";
                $leaders[4] = "/tourney-bracket-leaders/";
		$tbh2[2] = "Tiebreaker2";
                $tbh2[4] = "Champion";

		$html = view("json/bracket_leaders", ['data' => $data,'cuser_login' => $cuser_login, 'crnk' => $crnk, 'cpts' => $cpts, 'ctiebreaker' => $ctiebreaker, 'ctiebreaker2' => $ctiebreaker2, 'lnk' => $lnk[$game_type_id], 'leaders' => $leaders[$game_type_id], 'header' => $header[$game_type_id], 'tbh2' => $tbh2[$game_type_id]])->render();

                # list ($data) = $this->$templatec($template,$json_obj,$gender,$division);
                return array('','','',$html);
	}
}
