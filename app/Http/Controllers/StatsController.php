<?php

namespace App\Http\Controllers;
use Response;

class StatsController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
	
		$template = "overall";
		$gender = "";
		$division = "";
		$code = "";
		$full_season = "";
		$sitecode = "";

		if ($count > 0) $template = $parts[0];		
		if ($count > 1) $code = $parts[1];
		if ($count > 2) $full_season = $parts[2];
		
		$pattern = "/^division-/";
		
		if (preg_match($pattern,$code)) {
			$p2 = explode("-",$code);
			$ct2 =  count($p2);
			if ($ct2 > 2) {
				$division = strtoupper($p2[1]);
				$gender = $this->get_gender($p2[2]);
			} else {
				$gender = "m";
				$division = "I";
			}
		} else {
			$full_season = $this->get_current_full_season();
			if ($count > 3) $full_season = $parts[3];
			if ($template == "team") {
				$gender = $this->get_gender($parts[2]);
				$sitecode = $this->get_sitecode($gender,$code);
				$division = $sitecode['division'];		
				$code = $sitecode['code'];
			} elseif ($template == "conference") {
				if ($count > 2) $full_season = $parts[2];
				$sitecode = $this->get_confsitecode($code);
				$gender = $sitecode['gender'];
				$division = $sitecode['division'];		
				$code = $sitecode['code'];
			} elseif ($template == "player") {
				$p2 = explode(",",$code);
				$player_id = $p2[1];
			}	
		}

		if ($full_season == "") {
			$full_season = $this->get_current_full_season();
		}
		
		$season = $this->get_season($full_season);
		
		return array($template,$code,$gender,$division,$season,$sitecode,$full_season);
	}
	public function get_page_title($type,$code,$gender,$division,$season,$sitecode,$full_season)
	{
		$full_season = substr($season,0,4)."-".substr($season,4,4);
		
		$desc = "Stats";

		$dtype = str_replace("_template", "", $type);

		if ($type == "team_template") {
		    	$shortname = "";
		    	if (array_key_exists("shortname",$sitecode)) {$shortname = $sitecode['shortname']." ";}
			return $shortname.$this->get_full_gender($gender)."'s Hockey ".$full_season." ".$desc;
		} elseif ($type == "conference_template") {
			if (array_key_exists("shortname",$sitecode)) {$shortname = $sitecode['shortname']." ";}
			return $shortname.$this->get_full_gender($gender)."'s Division ".$division." ".ucfirst($dtype)." Hockey Statistics: ".$full_season;
		} else {
			return $this->get_full_gender($gender)."'s Division ".$division." ".ucfirst($dtype)." Hockey Statistics: ".$full_season." ".$desc;
		}
	}	
	public function template($template,$code,$gender,$division,$season,$sitecode,$full_season) {
		# parse_uri
		//list ($template,$code,$gender,$division,$season,$sitecode,$full_season) = $this->parseuri($uri);

		# load conf
		$page_title = $this->get_page_title($template,$code,$gender,$division,$season,$sitecode,$full_season);
		
		# composite is all in one file
		$filename = $this->get_jsonfilename("stats","composite",$gender,$division,$season,$sitecode);	
		
		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$templatec = str_replace("-","_",$template);
			
			if (property_exists($json_obj,"scoring")) {
				if (property_exists($json_obj->scoring,"national")) {
					if (property_exists($json_obj->scoring->national,"data")) {
						list ($data,$datatable,$meta_data,$page_title) = $this->$templatec($template,$json_obj,$code,$page_title,$gender,$division,$full_season);
						return Response::json(array('json' => $data, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title, 'gender' => $gender, 'division' => $division, 'full_season' => $full_season));
					}
				}
			}
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));		
	}
	public function overall($division,$full_gender){
		$gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
		$full_season = $this->get_current_full_season();
		$season = $this->get_season($full_season);
		return $this->template("overall_template","",$gender,$division,$season,"",$full_season);
	}
	public function overall_season($division,$full_gender,$full_season){
		$gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
		$season = $this->get_season($full_season);
		return $this->template("overall_template","",$gender,$division,$season,"",$full_season);
        }
	public function overall_template($template,$json,$code,$page_title,$gender,$division,$full_season){
		$template = "season";

	 	# attempt to load json files
		$datatable = [];
		$data = [];
		$data['dt1'] = [];
		$data['dt1']['data'] = [];
		$data['dt2'] = [];
		$data['dt2']['data'] = [];
		$data['dt3'] = [];
		$data['dt3']['data'] = [];
		$data['dt4'] = [];
		$data['dt4']['data'] = [];
		$data['dt5'] = [];
		$data['dt5']['data'] = [];
		$data['dt6'] = [];
		$data['dt6']['data'] = [];
		$data['dt7'] = [];
		$data['dt7']['data'] = [];				
		$data['dt8'] = [];
		$data['dt8']['data'] = [];	
		$data['dt9'] = [];
		$data['dt9']['data'] = [];	
		$data['dt10'] = [];
		$data['dt10']['data'] = [];				
		$data['dt11'] = [];
		$data['dt11']['data'] = [];					
		$data['dt12'] = [];
		$data['dt12']['data'] = [];	
		$data['dt13'] = [];
		$data['dt13']['data'] = [];	
		$data['dt14'] = [];
		$data['dt14']['data'] = [];	
		$data['dt15'] = [];
		$data['dt15']['data'] = [];	
		
		$season = str_replace( "-", "", $full_season );

		$datatable['dt1']['sub_title'] = "Scoring Leaders";
		$datatable['dt2']['sub_title'] = "Goals";
		$datatable['dt3']['sub_title'] = "Assists";
		$datatable['dt4']['sub_title'] = "Power Play Goals";
		$datatable['dt5']['sub_title'] = "Shorthanded Goals";
		
		$datatable['dt6']['sub_title'] = "Goaltending Leaders";
		$datatable['dt7']['sub_title'] = "Save Percentage";
		$datatable['dt8']['sub_title'] = "Minutes Played";
		$datatable['dt9']['sub_title'] = "Shutouts";				

		$datatable['dt10']['sub_title'] = "Team Offense";
		$datatable['dt11']['sub_title'] = "Team Defense";
		$datatable['dt12']['sub_title'] = "Power Play";
		$datatable['dt13']['sub_title'] = "Penalty Kill";
		$datatable['dt14']['sub_title'] = "Penalty Minutes";
		$datatable['dt15']['sub_title'] = "Shorthanded Goals";
		
		# load keys we will capture
		$key = $this->list_col();
		foreach ($json->scoring->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->pts);
			foreach ($key as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt1']['data'],$player);
		}
	
		$key = array("player_name","gp","g",'l7g','l14g','l28g');
		foreach ($json->scoring->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->g);
			foreach ($key as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt2']['data'],$player);
		}

		$key = array("player_name","gp","a",'l7a','l14a','l28a');
		foreach ($json->scoring->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->a);
			foreach ($key as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt3']['data'],$player);
		}

		$key = array("player_name","gp","ppg",'l7ppg','l14ppg','l28ppg');
		foreach ($json->scoring->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->ppg);
			foreach ($key as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt4']['data'],$player);
		}

		$key = array("player_name","gp","shg",'l7shg','l14shg','l28shg');
		foreach ($json->scoring->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->shg);
			foreach ($key as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt5']['data'],$player);
		}

		$keyg = $this->list_colg();
		if (property_exists($json->goaltending->national,"data")) {
		foreach ($json->goaltending->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->gaa);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt6']['data'],$player);
		}

		$keyg = array("player_name","gp","svp","l7svp","l14svp","l28svp");
		foreach ($json->goaltending->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->svp);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt7']['data'],$player);
		}

		$keyg = array("player_name","gp","min","l7min","l14min","l28min");
		foreach ($json->goaltending->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->min);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt8']['data'],$player);
		}

		$keyg = array("player_name","gp","sho","l7sho","l14sho","l28sho");
		foreach ($json->goaltending->national->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->sho);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt9']['data'],$player);
		}
		}

		$keyn = $this->list_coln();
		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->gpg);
			foreach ($keyn as $k) {
				array_push($team,$rec->$k);
			}
			array_push($data['dt10']['data'],$team);
		}

		$keyn = $this->list_coln();
		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->gapg);
			foreach ($keyn as $k) {
				array_push($team,$rec->$k);
			}
			array_push($data['dt11']['data'],$team);
		}

		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->ppp);
			array_push($team,$rec->shortname);
			array_push($team,$rec->gp);
			array_push($team,$rec->ppg."-of-".$rec->ppo);
			array_push($team,$rec->ppp);
			array_push($team,$rec->l7ppp);
			array_push($team,$rec->l14ppp);
			array_push($team,$rec->l28ppp);
			array_push($data['dt12']['data'],$team);
		}

		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->pkp);
			array_push($team,$rec->shortname);
			array_push($team,$rec->gp);
			array_push($team,$rec->ppoa-$rec->ppga."-of-".$rec->ppoa);
			array_push($team,$rec->pkp);
			array_push($team,$rec->l7pkp);
                        array_push($team,$rec->l14pkp);
                        array_push($team,$rec->l28pkp);
			array_push($data['dt13']['data'],$team);
		}

		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->pimpg);
			array_push($team,$rec->shortname);
			array_push($team,$rec->gp);
			array_push($team,$rec->pen."-for-".$rec->pim);
			array_push($team,$rec->pimpg);
			array_push($team,$rec->l7pimpg);
                        array_push($team,$rec->l14pimpg);
			array_push($team,$rec->l28pimpg);
			array_push($data['dt14']['data'],$team);
		}

		foreach ($json->national->team->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->shg);
			array_push($team,$rec->shortname);
			array_push($team,$rec->gp);
			array_push($team,$rec->shg);
			array_push($team,$rec->l7shg);
			array_push($team,$rec->l14shg);
			array_push($team,$rec->l28shg);
			array_push($data['dt15']['data'],$team);
		}
		
		$data['dt1']['meta_data'] = $json->scoring->meta_data;
		$data['dt2']['meta_data'] = $json->goals->meta_data;
		$data['dt3']['meta_data'] = $json->assists->meta_data;
		$data['dt4']['meta_data'] = $json->ppg->meta_data;
		$data['dt5']['meta_data'] = $json->shg->meta_data;
		
		$data['dt6']['meta_data'] = $json->goaltending->meta_data;
		$data['dt7']['meta_data'] = $json->svp->meta_data;
		$data['dt8']['meta_data'] = $json->minutes->meta_data;
		$data['dt9']['meta_data'] = $json->sho->meta_data;
		
		$data['dt10']['meta_data'] = $json->offense->meta_data;
		$data['dt11']['meta_data'] = $json->defense->meta_data;
		$data['dt12']['meta_data'] = $json->nppp->meta_data;
		$data['dt13']['meta_data'] = $json->npkp->meta_data;
		$data['dt14']['meta_data'] = $json->npim->meta_data;
		$data['dt15']['meta_data'] = $json->nshg->meta_data;
		
		return array($data,$datatable,"",$page_title);
	}
	public function team($code,$full_gender){
                $gender = $this->get_gender($full_gender);
                $full_season = $this->get_current_full_season();
		$season = $this->get_season($full_season);
		$sitecode = $this->get_sitecode($gender,$code);
                $division = $sitecode['division'];
                $code = $sitecode['code'];
                return $this->template("team_template",$code,$gender,$division,$season,$sitecode,$full_season);
        }
        public function team_season($code,$full_gender,$full_season){
                $gender = $this->get_gender($full_gender);
		$season = $this->get_season($full_season);
		$sitecode = $this->get_sitecode($gender,$code);
                $division = $sitecode['division'];
                $code = $sitecode['code'];
                return $this->template("team_template",$code,$gender,$division,$season,$sitecode,$full_season);
        }
	public function team_template($template,$json,$code,$page_title,$gender,$division,$full_season){			
	 	# attempt to load json files
		$datatable = [];
		$data = [];
		$data['dt1'] = [];
		$data['dt1']['data'] = [];
		$data['dt2'] = [];
		$data['dt2']['data'] = [];
		$data['dt3'] = [];
		$data['dt3']['data'] = [];

		$season = str_replace( "-", "", $full_season );

		$datatable['dt1']['sub_title'] = "Scoring Leaders";
		$datatable['dt2']['sub_title'] = "Goaltending";
		
		# load keys we will capture
		$key = $this->list_col();
		foreach ($json->scoring->national->data as $rec) {
			if ($rec->code == $code) {
				$player = [];
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt1']['data'],$player);
			}
		}
		
		$keyg = $this->list_colg();
		foreach ($json->goaltending->overall->data as $rec) {
			if ($rec->code == $code) {
				$player = [];
				foreach ($keyg as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt2']['data'],$player);
			}
		}

		$data['dt1']['meta_data'] = $json->tscoring->meta_data;
		$data['dt2']['meta_data'] = $json->tgoaltending->meta_data;
				
		return array($data,$datatable,$json->scoring->meta_data,$page_title);	
	}
	public function conference($code){
                $full_season = $this->get_current_full_season();
                $season = $this->get_season($full_season);
		$sitecode = $this->get_confsitecode($code);
                $division = $sitecode['division'];
                $gender = $sitecode['gender'];
		$code = $sitecode['code'];
                return $this->template("conference_template",$code,$gender,$division,$season,$sitecode,$full_season);
        }
        public function conference_season($code,$full_season){
		$season = $this->get_season($full_season);
		$sitecode = $this->get_confsitecode($code);

                $division = $sitecode['division'];
		$gender = $sitecode['gender'];
		$code = $sitecode['code'];

                return $this->template("conference_template",$code,$gender,$division,$season,$sitecode,$full_season);
        }
	public function conference_template($template,$json,$code,$page_title,$gender,$division,$full_season){
	 	# attempt to load json files
		$datatable = [];
		$data = [];
		$data['dt1'] = [];
		$data['dt1']['data'] = [];
		$data['dt2'] = [];
		$data['dt2']['data'] = [];
		$data['dt3'] = [];
		$data['dt3']['data'] = [];
		$data['dt4'] = [];
		$data['dt4']['data'] = [];
		$data['dt5'] = [];
		$data['dt5']['data'] = [];
		$data['dt6'] = [];
		$data['dt6']['data'] = [];
		$data['dt7'] = [];
		$data['dt7']['data'] = [];				
		$data['dt8'] = [];
		$data['dt8']['data'] = [];	
		$data['dt9'] = [];
		$data['dt9']['data'] = [];	
		$data['dt10'] = [];
		$data['dt10']['data'] = [];	
		$data['dt11'] = [];
		$data['dt11']['data'] = [];					
		$data['dt12'] = [];
		$data['dt12']['data'] = [];	
		$data['dt13'] = [];
		$data['dt13']['data'] = [];	
		$data['dt14'] = [];
		$data['dt14']['data'] = [];	
		$data['dt15'] = [];
		$data['dt15']['data'] = [];	
												
		$season = str_replace( "-", "", $full_season );

		$datatable['dt1']['sub_title'] = "Scoring Leaders";
		$datatable['dt2']['sub_title'] = "Goals";
		$datatable['dt3']['sub_title'] = "Assists";
		$datatable['dt4']['sub_title'] = "Power Play Goals";
		$datatable['dt5']['sub_title'] = "Shorthanded Goals";
		
		$datatable['dt6']['sub_title'] = "Goaltending Leaders";
		$datatable['dt7']['sub_title'] = "Save Percentage";
		$datatable['dt8']['sub_title'] = "Minutes Played";
		$datatable['dt9']['sub_title'] = "Shutouts";				

		$datatable['dt10']['sub_title'] = "Team Offense";
		$datatable['dt11']['sub_title'] = "Team Defense";
		$datatable['dt12']['sub_title'] = "Power Play";
		$datatable['dt13']['sub_title'] = "Penalty Kill";
		$datatable['dt14']['sub_title'] = "Penalty Minutes";
		$datatable['dt15']['sub_title'] = "Shorthanded Goals";
		
		# load keys we will capture
		$key = array("player_name","cgp","cg","ca","cpts","cptsgp","cgwg","cppg","cshg","l7gp","l7g","l7a","l7pts","l14gp","l14g","l14a","l14pts","l28gp","l28g","l28a","l28pts");
		foreach ($json->scoring->conf->$code->data as $rec) {
			if ($rec->cpts > 0) {
				$player = [];
				array_push($player,$rec->rnk->cpts);
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt1']['data'],$player);
			}
		}
	
		$key = array("player_name","cgp","cg",'l7g','l14g','l28g');
		foreach ($json->scoring->conf->$code->data as $rec) {
			if ($rec->cg > 0) {			
				$player = [];
				array_push($player,$rec->rnk->cg);
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt2']['data'],$player);
			}
		}

		$key = array("player_name","cgp","ca",'l7a','l14a','l28a');
		foreach ($json->scoring->conf->$code->data as $rec) {
			if ($rec->ca > 0) {
				$player = [];
				array_push($player,$rec->rnk->ca);
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt3']['data'],$player);
			}
		}

		$key = array("player_name","cgp","cppg",'l7ppg','l14ppg','l28ppg');
		foreach ($json->scoring->conf->$code->data as $rec) {
			if ($rec->cppg > 0) {
				$player = [];
				array_push($player,$rec->rnk->cppg);
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt4']['data'],$player);
			}
		}

		$key = array("player_name","cgp","cshg",'l7shg','l14shg','l28shg');
		foreach ($json->scoring->conf->$code->data as $rec) {
			if ($rec->cshg > 0) {
				$player = [];
				array_push($player,$rec->rnk->cshg);
				foreach ($key as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt5']['data'],$player);
			}
		}

		$keyg = array("player_name","cgp","cw","cl","ct","cwp","cmin","cga","csaves","csvp","cgaa","csho","l7rec","l7svp","l7gaa","l14rec","l14svp","l14gaa","l28rec","l28svp","l28gaa");
		foreach ($json->goaltending->conf->$code->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->cgaa);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt6']['data'],$player);
		}

		$keyg = array("player_name","cgp","csvp","l7min","l14svp","l28svp");
		foreach ($json->goaltending->conf->$code->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->csvp);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt7']['data'],$player);
		}

		$keyg = array("player_name","cgp","cmin","l7min","l14min","l28min");
		foreach ($json->goaltending->conf->$code->data as $rec) {
			$player = [];
			array_push($player,$rec->rnk->cmin);
			foreach ($keyg as $k) {
				array_push($player,$rec->$k);
			}
			array_push($data['dt8']['data'],$player);
		}

		$keyg = array("player_name","cgp","csho","l7sho","l14sho","l28sho");
		foreach ($json->goaltending->conf->$code->data as $rec) {
			if ($rec->csho > 0) {
				$player = [];
				array_push($player,$rec->rnk->csho);
				foreach ($keyg as $k) {
					array_push($player,$rec->$k);
				}
				array_push($data['dt9']['data'],$player);
			}
		}

		$keyn = $this->list_coln();
		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cgpg);
			foreach ($keyn as $k) {
				array_push($team,$rec->$k);
			}
			array_push($data['dt10']['data'],$team);
		}

		$keyn = $this->list_coln();
		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cgapg);
			foreach ($keyn as $k) {
				array_push($team,$rec->$k);
			}
			array_push($data['dt11']['data'],$team);
		}

		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cppp);
			array_push($team,$rec->shortname);
			array_push($team,$rec->cgp);
			array_push($team,$rec->cppg."-of-".$rec->cppo);
			array_push($team,$rec->cppp);
			array_push($team,$rec->l7ppp);
			array_push($team,$rec->l14ppp);
			array_push($team,$rec->l28ppp);
			array_push($data['dt12']['data'],$team);
		}

		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cpkp);
			array_push($team,$rec->shortname);
			array_push($team,$rec->cgp);
			array_push($team,$rec->cppoa-$rec->cppga."-of-".$rec->cppoa);
			array_push($team,$rec->cpkp);
			array_push($team,$rec->l7pkp);
                        array_push($team,$rec->l14pkp);
                        array_push($team,$rec->l28pkp);
			array_push($data['dt13']['data'],$team);
		}

		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cpimpg);
			array_push($team,$rec->shortname);
			array_push($team,$rec->cgp);
			array_push($team,$rec->cpen."-for-".$rec->cpim);
			array_push($team,$rec->cpimpg);
			array_push($team,$rec->l7pimpg);
                        array_push($team,$rec->l14pimpg);
                        array_push($team,$rec->l28pimpg);
			array_push($data['dt14']['data'],$team);
		}

		foreach ($json->conf->$code->data as $rec) {
			$team = [];
			array_push($team,$rec->rnk->cshg);
			array_push($team,$rec->shortname);
			array_push($team,$rec->cgp);
			array_push($team,$rec->cshg);
			array_push($team,$rec->l7shg);
                        array_push($team,$rec->l14shg);
                        array_push($team,$rec->l28shg);
			array_push($data['dt15']['data'],$team);
		}

		$data['dt1']['meta_data'] = $json->scoring->meta_data;
		$data['dt2']['meta_data'] = $json->goals->meta_data;
		$data['dt3']['meta_data'] = $json->assists->meta_data;
		$data['dt4']['meta_data'] = $json->ppg->meta_data;
		$data['dt5']['meta_data'] = $json->shg->meta_data;

		$data['dt6']['meta_data'] = $json->goaltending->meta_data;
		$data['dt7']['meta_data'] = $json->svp->meta_data;
		$data['dt8']['meta_data'] = $json->minutes->meta_data;
		$data['dt9']['meta_data'] = $json->sho->meta_data;

		$data['dt10']['meta_data'] = $json->offense->meta_data;
		$data['dt11']['meta_data'] = $json->defense->meta_data;

		$data['dt12']['meta_data'] = $json->nppp->meta_data;
		$data['dt13']['meta_data'] = $json->npkp->meta_data;
		$data['dt14']['meta_data'] = $json->npim->meta_data;
		$data['dt15']['meta_data'] = $json->nshg->meta_data;

		return array($data,$datatable,"",$page_title);
	}
	public function player($template,$json,$code,$page_title,$gender,$division,$full_season){
		return array($data,$datatable,"",$page_title);
	}
	public function list_col(){
		return array("player_name","gp","g","a","pts","ptsgp","gwg","ppg","shg","l7gp","l7g","l7a","l7pts","l14gp","l14g","l14a","l14pts","l28gp","l28g","l28a","l28pts");
	}
	public function list_colg(){
		return array("player_name","gp","w","l","t","wp","min","ga","saves","svp","gaa","sho","l7rec","l7svp","l7gaa","l14rec","l14svp","l14gaa","l28rec","l28svp","l28gaa");
	}
	public function list_coln(){
		return array("shortname","gp","g","gpg","ga","gapg","ppg","ppo","ppp","ppga","ppoa","pkp","shg","shga","l7gp","l7gpg","l7gapg","l7ppp","l7pkp","l14gp","l14gpg","l14gapg","l14ppp","l14pkp","l28gp","l28gpg","l28gapg","l28ppp","l28pkp");
	}		
}
