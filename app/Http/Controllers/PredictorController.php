<?php

namespace App\Http\Controllers;
use Response;

class PredictorController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "pwpresults";
		$gender = "";
		$division = "";
		$season = "";
		$uniqueURI = "";
		
                if ($count>0) {$template = $parts[0];}
		if ($count>1) {$gender = $parts[1];}
		if ($count>2) {$division = $parts[2];}
		if ($count>3) {$season = $parts[3];}
		if ($count>4) {$uniqueURI = $parts[4];}
		$ext= "";
		
		return array($template,$gender,$division,$season,$uniqueURI,$ext);
	}

	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($template,$gender,$division,$season,$uniqueURI,$ext) = $this->parseuri($uri);

		list($data,$html) = $this->$template($gender,$division,$season,$uniqueURI,$ext);
		
		if ($data == '' && $html == '') {
			$html = "Result not available.";
		}


		return Response::json(array('html' => $html, 'json' => $data));
	}

        public function analyzer($gender,$division,$season,$uniqueURI,$ext){
                $filename = config('services.JSON_DIR')."bracketcontest/pwpanalyzer-$season.json";
                if (file_exists($filename)) {
			$fdata = file_get_contents($filename);
                        $data = json_decode($fdata,TRUE);
	
		        usort($data['tot'], function ($item1, $item2) {
                                return $item1['wgt'] <=> $item2['wgt'];
                        });

			ksort($data['active_games']);

			$seeds = $this->tstruct($gender,$division,$season,$ext);

			$html = view("json/pwpanalyzer", ['data' => $data, 'cdd' => $seeds['cdd']])->render();
                        return array('',$html);
		}
	}

	public function pwpresults($gender,$division,$season,$uniqueURI,$ext){
		$con = $this->_init_db();

		if ($ext == 0) {$ext = "";}

		$sql = "
SELECT *
FROM gd$season$ext.pwpn
WHERE uniqueURI = '$uniqueURI'";
		//echo $sql;

                $res=mysqli_query($con,$sql);
		$data = [];
		$data['teams'] = [];
		$data['chmp'] = [];
		$data['picks'] = [];
		$data['pwrdata'] = [];

//		$conf = $this->get_confs($gender,$division);

                if($res != "") {
			$d = mysqli_fetch_object($res);

                        $sup_data =  json_decode($d->sup_data, TRUE);

			for ($i = 1; $i <= 16; $i++) {
				$tc = "t".$i;
				$tdata = $this->team_map($gender,$d->$tc);
				$data['teams'][$tdata->shortname] = 1 ;
			}

			$sdata = array();
			foreach ($sup_data as $tc => $res) {
				$res['rnkadj'] = $res['pts']+$res['adjrpiqwb'];
				$res['team_name'] = $tc;
                                $res['ncaa'] = 0;
				if(array_key_exists($tc,$data['teams'])) {$res['ncaa'] = 1;};
				array_push($sdata,$res);
                        }

			usort($sdata, function ($item1, $item2) {
    				return $item2['rnkadj'] <=> $item1['rnkadj'];
			});
			$data['pwrdata'] = $sdata;

			for ($i = 1; $i <= 6; $i++) {
				$chmp = "chmp$i";
				$altaq = "altaq$i";
				$tdata = $this->team_map($gender,$d->$chmp);
				if ($d->$altaq) {
					$tdata = $this->team_map($gender,$d->$altaq);
					$d->$chmp = "";
				}
                                $data['chmp'][$tdata->shortname] = $i;
			}

			$picks =  json_decode($d->storage_serial, TRUE);
			$upick = [];

			$seeds = $this->tstruct($gender,$division,$season,$ext);
			$upick = $seeds['cdd'];

			foreach ($picks AS $code => $pick) {
                        	if ($pick == "tie") {
                                	$upick[$code]['team']="Tie";
                                } else {
                                	$tdata = $this->team_map($gender,$pick);
                                        $upick[$code]['team']=$tdata->shortname;
                                }
                        }

			
			$data['uniqueID'] = $uniqueURI;
			$data['picks'] = $upick;
		
			$html = view("json/pwpresults", ['data' => $data])->render();
			return [ 'html' => $html, 'view' => 'result', 'picks' => $picks, 'uniqueURI' => $uniqueURI ];
		} else {
			return [ 'html' => '', 'view' => '', 'uniqueURI' => $uniqueURI ];
		}
	}

	private function tstruct($gender,$division,$season,$ext) {
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
				$cdd = [];
				$cdata = $this->conf_map($conf);

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

					$seeds['cdd'][$conf.$gt]['gdesc'] = $cdata->shortname." ".$struct->desc->$gt;
                                }
                      		$rec->gm = $cgm;
                        }
		}
		return $seeds;
	}

        public function seeds($gender,$division,$season,$ext) {
		$seeds = $this->tstruct($gender,$division,$season,$ext);
		$seeds['view'] = 'picks';
                if (array_key_exists('games', $seeds)) {
                        $filename = config('services.JSON_DIR')."bracketcontest/gt2-".$season.".json";
                        // echo $filename;
                        if (file_exists($filename)) {
                                $gt = file_get_contents($filename,true);
                                $dt = json_decode($gt,TRUE);
				$gres = [];

				foreach ($dt['games'] as $gcode => $rec) {
					if ($rec['gt'] != "f") {
					//echo $rec['gt'];
				 	$tdata = $this->team_map($gender,$rec['win_code']);
					$ltdata = $this->team_map($gender,$rec['lose_code']);
					$gres[$gcode] = $rec;
                                        $gres[$gcode]['name'] = $tdata->shortname;
					$gres[$gcode]['lname'] = $ltdata->shortname;
					}
				}
                                $seeds['init']['res'] = $gres;
                        }

			return $seeds;
                }
        }
}
