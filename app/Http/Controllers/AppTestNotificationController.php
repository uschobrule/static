<?php

namespace App\Http\Controllers;

use Response;
use View;

class AppTestNotificationController extends AppController
{	
    public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
	
		$platform = $parts[0];
		$token = $parts[1];
		$auth_key_in = $parts[2];
		$favDiv = $parts[3];
		$favTeam = $parts[4];

		$auth_key_data = md5($token."uscho.".$platform);
		$pass = 1;

		if (strcmp($auth_key_in,$auth_key_data)) {
			$pass = 0;
		}

		$kvp = [];	
		if ($count >= 11) {
			$kvp[$parts[5]] = $parts[6];
			$kvp[$parts[7]] = $parts[8]; 
			$kvp[$parts[9]] = $parts[10]; 
		} else if ($count >= 9) {
			$kvp[$parts[5]] = $parts[6];
                        $kvp[$parts[7]] = $parts[8];
		} else if ($count >= 7) {
			$kvp[$parts[5]] = $parts[6];
		}

		return array($platform,$token,$favDiv,$favTeam,$kvp,$pass);
	}
	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($platform,$token,$favDiv,$favTeam,$kvp,$pass) = $this->parseuri($uri);
		if ($pass == 0) {
			return Response::json(array('success' => 0));
		}
		return $this->sync($platform,$token,$favDiv,$favTeam,$kvp);
	}

	public function sync($platform,$token,$auth_key_in,$favDiv,$favTeam,$goalNotification,$periodNotification,$gameNotification)
	{

		$auth_key_data = md5($token."uscho.".$platform);

                if (strcmp($auth_key_in,$auth_key_data)) {
                	return Response::json(array('success' => 0));
                }

		$kvp = ["goal"=>$goalNotification,"period"=>$periodNotification,"game"=>$gameNotification];

		// load notification type
		$nottype = ["1" => "goal", "2" => "period", "3" => "game"];
	
		// see what types are register for this token
		$con = $this->_init_db();
        	$pnu= $this->test_token($con,$platform,$token);

		$puntd = [];
		$push_notification_user_id = 0;

		// test to see if push not user exists
		if (mysqli_num_rows($pnu) == 0) {
			$insertpnu = "INSERT INTO userdata.push_notification_user (platform,token,fav_div,fav_team,date_created) VALUES ('$platform','$token','$favDiv','$favTeam',now())";
			mysqli_query($con,$insertpnu);
			$push_notification_user_id = mysqli_insert_id($con);
		} else {
			$push_notification_user_id = 0;
			while($pnud = mysqli_fetch_object($pnu)) {
				if ($push_notification_user_id  == 0) {
					$push_notification_user_id = $pnud->push_notification_user_id;	
					$data_fav_team = $pnud->fav_team;
				}
				// test for update of user selections
				if ($pnud->notification_type_id > 0) {
					if (array_key_exists($nottype[$pnud->notification_type_id],$kvp)) {
						$value = $kvp[$nottype[$pnud->notification_type_id]];
						if (strcmp($value,$pnud->value)) {
							$updatepnud = "UPDATE userdata.push_user_notification_type SET value = '$value' WHERE pun_id = $pnud->pun_id";
		                //                echo $updatepnud;
       		                         		mysqli_query($con,$updatepnud);
						}

						$puntd[$pnud->notification_type_id] = $value;
					}
				}
			}

			// test fav team change
			if (strcmp($data_fav_team,$favTeam)) {
				$updatepnu = "UPDATE userdata.push_notification_user SET fav_div='$favDiv', fav_team = '$favTeam' WHERE push_notification_user_id = $push_notification_user_id";
                       		//echo $updatepnu;
				mysqli_query($con,$updatepnu);
			}
		}

		// loop through not type and make sure 
		foreach($nottype as $type_id => $type_value) {
			if (!array_key_exists($type_id,$puntd)) {
				if (array_key_exists($type_value,$kvp)) {
					$value = $kvp[$type_value];
//					echo "ddadd $type_id $value";
					$insertpunt = "INSERT INTO userdata.push_user_notification_type (push_notification_user_id,notification_type_id,value) VALUES ('$push_notification_user_id','$type_id','$value')";
//       		        	echo $insertpunt; 
					mysqli_query($con,$insertpunt);
				}
			}
		}

		return Response::json(array('success' => 1));
	}

	public function test_token($con,$platform,$token) {

		$sqlpnu = "SELECT pnu.*,punt.notification_type_id,punt.value,punt.pun_id
FROM userdata.push_notification_user pnu 
LEFT JOIN userdata.push_user_notification_type punt ON pnu.push_notification_user_id = punt.push_notification_user_id
WHERE pnu.platform = '$platform' AND pnu.token = '$token'";
	
        	return mysqli_query($con,$sqlpnu);
	}
}
