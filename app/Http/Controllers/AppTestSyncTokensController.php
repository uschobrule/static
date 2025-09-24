<?php

namespace App\Http\Controllers;

use Response;
use View;

class AppTestSyncTokensController extends AppTestNotificationController
{	
    public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
	
		$platform = $parts[0];
		$token = $parts[1];
		$old_token = $parts[2];
		$auth_key_in = $parts[3];
		$favDiv = "NA";
		$favTeam = "NA";
		$kvp = [];
		$pass = 1;

		$auth_key_data = md5($token.$old_token."uscho.".$platform);
                $pass = 1;

                if (strcmp($auth_key_in,$auth_key_data)) {
                	$pass = 0;
                }
	
		if ($count > 5) {
               		$favDiv = $parts[4];
             	   	$favTeam = $parts[5];

			if ($count >= 12) {
				$kvp[$parts[6]] = $parts[7];
				$kvp[$parts[8]] = $parts[9]; 
				$kvp[$parts[10]] = $parts[11]; 
			} else if ($count >= 10) {
				$kvp[$parts[6]] = $parts[7];
       		                $kvp[$parts[8]] = $parts[9];
			} else if ($count >= 8) {
				$kvp[$parts[6]] = $parts[7];
			}
		}

		return array($platform,$token,$old_token,$pass,$favDiv,$favTeam,$kvp);
	}

	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($platform,$token,$old_token,$pass,$favDiv,$favTeam,$kvp) = $this->parseuri($uri);
		if ($pass == 0) {
			return Response::json(array('success' => 0));
		}
		return $this->sync_tokens($platform,$token,$old_token,$favDiv,$favTeam,$kvp);
	}

	public function sync_tokens($platform,$token,$old_token,$auth_key_in,$favDiv,$favTeam,$goalNotification,$periodNotification,$gameNotification)
	{

		$auth_key_data = md5($token."uscho.".$platform);

                if (strcmp($auth_key_in,$auth_key_data)) {
                	return Response::json(array('success' => 0));
                }

		$kvp = ["goal"=>$goalNotification,"period"=>$periodNotification,"game"=>$gameNotification];
		
		// see what types are register for this token
		$con = $this->_init_db();

		$pnu= $this->test_token($con,$platform,$old_token);

                $puntd = [];
                $push_notification_user_id = 0;

                // test to see if push not user exists
                if (mysqli_num_rows($pnu) == 0) {
			$this->sync($platform,$token,$favDiv,$favTeam,$kvp);
		} else {
        		$update = "UPDATE userdata.push_notification_user SET token = '$token' WHERE platform = '$platform' AND token = '$old_token'";
			mysqli_query($con,$update);
		}

		return Response::json(array('success' => 1));
	}
}
