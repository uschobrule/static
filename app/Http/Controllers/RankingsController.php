<?php

namespace App\Http\Controllers;

use Response;
use View;

class RankingsController extends JSONController
{	
    public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);
		
		$template =  $parts[0];
		$p2 = explode("-",$parts[0]);
		$c2 = count($p2);
		
		$gender = "";
		$full_gender = "";
		$division = "";
		$date = "current";
		
		if ($c2 > 3) {
			$template = $p2[3];
			$gender = $this->get_gender($p2[2]);
			$full_gender = $p2[2];
			$division = strtoupper($p2[1]);
			if ($count > 1) $date = $parts[1];
		} elseif($count > 1) {
			$p2 = explode("-",$parts[1]);
			$c2 = count($p2);
			$full_gender = $p2[2];
			$gender = $this->get_gender($p2[2]);
			$division = strtoupper($p2[1]);	
			if ($count > 2) {
				$date = $parts[2];
			}
		}

		if ($date == "") {
			$season = $this->get_season("");
			$date = substr($season,0,4)."-".substr($season,-4);
		}

		$templatec = str_replace("-","_",$template);
		
		return array($templatec,$gender,$division,$date,$full_gender);
	}
	public function missingMethod($uri = array())
	{
		# parse_uri
		list ($template,$gender,$division,$date,$full_gender) = $this->parseuri($uri);
		return $this->$template($gender,$division,$date,$full_gender);
	}
	public function allpolls()
	{
		$date = "current";
		$html = "";
                list($phtml,$meta_data,$datatable,$page_title) = $this->pollhtml("m","I",$date,"mens","1");
		$html .= $phtml;
		list($phtml,$meta_data,$datatable,$page_title) = $this->pollhtml("w","I",$date,"womens","2");
                $html .= "<br>".$phtml;
		list($phtml,$meta_data,$datatable,$page_title) = $this->pollhtml("m","III",$date,"mens","3");
                $html .= "<br>".$phtml;
		list($phtml,$meta_data,$datatable,$page_title) = $this->pollhtml("w","III",$date,"womens","4");
                $html .= "<br>".$phtml;

                return Response::json(array('json' => [], 'html' =>  $html, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
	}

	public function poll($division,$full_gender,$date = "current") {
		$gender = $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
                list($html,$meta_data,$datatable,$page_title) = $this->pollhtml($gender,$division,$date,$full_gender,"1");
                return Response::json(array('json' => [], 'html' =>  $html, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
        }
        
    public function pollemail($division,$full_gender,$date = "current") {
		$gender = $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
                list($html,$meta_data,$datatable,$page_title) = $this->pollemailhtml($gender,$division,$date,$full_gender,"1");
                return Response::json(array('json' => [], 'html' =>  $html, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
        }
    
    public function pollimage($division,$full_gender,$date = "current") {
		$gender = $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
                list($html,$meta_data,$datatable,$page_title) = $this->pollimagehtml($gender,$division,$date,$full_gender,"1");
                return Response::json(array('json' => [], 'html' =>  $html, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
        }

	public function pollhtml($gender,$division,$date,$full_gender,$ind)
	{
		# poll is all in one file
		$json_obj = $this->poll_data();
		
		$data = [];
		$page_title = "";
		$html = "";
		$meta_data;
		$datatable['poll']['sub_title'] = "";
		
		$meta_data = $json_obj->meta_data;
			
		$polldate = $json_obj->poll_data->$gender->$division->$date->PollDate;
		$pd = $json_obj->poll_data->$gender->$division->$polldate;
		$page_title = "USCHO ".$pd->PollName." - ".$pd->full_poll_date;
		$data = $json_obj->data->$gender->$division->$polldate;
			
		foreach ($data->data as $rec) {
			$rec->prev_rnk = $this->get_team_rank($json_obj,$gender,$division,$rec->shortname,$pd->PrePollDate);
			if ($rec->prev_rnk == "") {$rec->prev_rnk = "NR";}
		}

		$pdate = [];
		$py = 1996;
			
		foreach ($json_obj->data->$gender->$division as $tdate => $rec) {
			$pd2 = $json_obj->poll_data->$gender->$division->$tdate;
			if (property_exists($pd2,"full_poll_date")) {
				$ymd = explode("-",$tdate);
				if ($ymd[1] > 7 && $py != $ymd[0]) {
					$pdate[$ymd[0]."-07"] = "--- ".($ymd[0])." - ".($ymd[0]+1)." ---";
					$py = $ymd[0];
				}
				$pdate[$tdate] = $pd2->full_poll_date;
			}
		}
			
		krsort($pdate);

    		$html = view("json/poll", ['json' => $json_obj->data->$gender->$division->$polldate, 'pd' => $pdate, 'ind' => $ind, 'page_title' => $page_title,'template' => strtolower('d-'.$division."-".$full_gender."-poll")])->render();
		return array(preg_replace( "/\r|\n|\t|/", "", $html ), $meta_data,$datatable,$page_title);
	}
	
	public function pollemailhtml($gender,$division,$date,$full_gender,$ind)
	{
		# poll is all in one file
		$json_obj = $this->poll_data();
		
		$data = [];
		$page_title = "";
		$html = "";
		$meta_data;
		$datatable['poll']['sub_title'] = "";
		
		$meta_data = $json_obj->meta_data;
			
		$polldate = $json_obj->poll_data->$gender->$division->$date->PollDate;
		$pd = $json_obj->poll_data->$gender->$division->$polldate;
		$page_title = "USCHO ".$pd->PollName." - ".$pd->full_poll_date;
		$data = $json_obj->data->$gender->$division->$polldate;
		$pollweek = $pd->full_poll_date;
			
		foreach ($data->data as $rec) {
			$rec->prev_rnk = $this->get_team_rank($json_obj,$gender,$division,$rec->shortname,$pd->PrePollDate);
			if ($rec->prev_rnk == "") {$rec->prev_rnk = "NR";}
		}

		if ($gender=="m" && $division=="I") {
			$voters = 50;
		} elseif ($gender=="m" && $division=="III") {
			$voters = 20;
		} elseif ($gender=="w") {
			$voters = 15;
		}

		$pdate = [];
		$py = 1996;
			
		foreach ($json_obj->data->$gender->$division as $tdate => $rec) {
			$pd2 = $json_obj->poll_data->$gender->$division->$tdate;
			if (property_exists($pd2,"full_poll_date")) {
				$ymd = explode("-",$tdate);
				if ($ymd[1] > 7 && $py != $ymd[0]) {
					$pdate[$ymd[0]."-07"] = "--- ".($ymd[0])." - ".($ymd[0]+1)." ---";
					$py = $ymd[0];
				}
				$pdate[$tdate] = $pd2->full_poll_date;
			}
		}
			
		krsort($pdate);

    		$html = view("json/pollemail", ['json' => $json_obj->data->$gender->$division->$polldate, 'pd' => $pdate, 'pollweek' => $pollweek, 'ind' => $ind, 'full_gender' => ucwords(str_replace("s","'s",$full_gender)), 'voters' => $voters, 'division' => $division, 'page_title' => $page_title,'template' => strtolower('d-'.$division."-".$full_gender."-poll")])->render();
		return array(preg_replace( "/\r|\n|\t|/", "", $html ), $meta_data,$datatable,$page_title);
	}
	public function pollimagehtml($gender,$division,$date,$full_gender,$ind)
	{
		# poll is all in one file
		$json_obj = $this->poll_data();
		
		$data = [];
		$page_title = "";
		$html = "";
		$meta_data;
		$datatable['poll']['sub_title'] = "";
		
		$meta_data = $json_obj->meta_data;
			
		$polldate = $json_obj->poll_data->$gender->$division->$date->PollDate;
		$pd = $json_obj->poll_data->$gender->$division->$polldate;
		$page_title = "USCHO ".$pd->PollName." - ".$pd->full_poll_date;
		$data = $json_obj->data->$gender->$division->$polldate;
		$pollweek = $pd->full_poll_date;
		
		if ($gender=="m") {
			$font = "1.6em";
		} elseif ($gender=="w") {
			$font = "1.1em";
		}
		foreach ($data->data as $rec) {
			$rec->prev_rnk = $this->get_team_rank($json_obj,$gender,$division,$rec->shortname,$pd->PrePollDate);
			if ($rec->prev_rnk == "") {$rec->prev_rnk = "NR";}
		}

		$pdate = [];
		$py = 1996;
			
		foreach ($json_obj->data->$gender->$division as $tdate => $rec) {
			$pd2 = $json_obj->poll_data->$gender->$division->$tdate;
			if (property_exists($pd2,"full_poll_date")) {
				$ymd = explode("-",$tdate);
				if ($ymd[1] > 7 && $py != $ymd[0]) {
					$pdate[$ymd[0]."-07"] = "--- ".($ymd[0])." - ".($ymd[0]+1)." ---";
					$py = $ymd[0];
				}
				$pdate[$tdate] = $pd2->full_poll_date;
			}
		}
			
		krsort($pdate);

    		$html = view("json/pollimage", ['json' => $json_obj->data->$gender->$division->$polldate, 'pd' => $pdate, 'pollweek' => $pollweek, 'ind' => $ind, 'full_gender' => ucwords(str_replace("s","'s",$full_gender)), 'division' => $division, 'page_title' => $page_title, 'font' => $font, 'template' => strtolower('d-'.$division."-".$full_gender."-poll")])->render();
		return array(preg_replace( "/\r|\n|\t|/", "", $html ), $meta_data,$datatable,$page_title);


	}
	public function toppoll($gender,$division,$date,$full_gender) {
		# poll is all in one file
		$data['I']['m']['code'] = $this->toppollgd("m","I",$date);
		$data['III']['m']['code'] = $this->toppollgd("m","III",$date);
		$data['I']['w']['code'] = $this->toppollgd("w","I",$date);
		$data['III']['w']['code'] = $this->toppollgd("w","III",$date);

		$tdata = file_get_contents(config('services.JSON_DIR')."site/team.json");
		$json = json_decode($tdata,true);
	
		foreach ($json as $gender => $rec) {
			foreach ($rec as $div => $team) {
				$tm[$team['code']] = $team['shortname'];			
			}
		}

                $data['I']['m']['hl'] = "Men's<br>D-I";
                $data['III']['m']['hl'] = "Men's<br>D-III";
                $data['I']['w']['hl'] = "Women's<br>D-I";
                $data['III']['w']['hl'] = "Women's<br>D-III";

                $data['I']['m']['link'] = "d-i-mens-poll";
                $data['III']['m']['link'] = "d-iii-mens-poll";
                $data['I']['w']['link'] = "d-i-womens-poll";
                $data['III']['w']['link'] = "d-iii-womens-poll";

		$data['I']['m']['shortname'] = $tm[$data['I']['m']['code']];
                $data['III']['m']['shortname'] = $tm[$data['III']['m']['code']];
                $data['I']['w']['shortname'] = $tm[$data['I']['w']['code']];
                $data['III']['w']['shortname'] = $tm[$data['III']['w']['code']];

		$html = view("json/toppoll", ['data' => $data])->render();
		return Response::json(array('html' => $html)); # "", 'json' => $data));
	}
	public function toppollnew() {
		$date = "current";

		# poll is all in one file
		$data['I']['m']['code'] = $this->toppollgd("m","I",$date);
		$data['III']['m']['code'] = $this->toppollgd("m","III",$date);
		$data['I']['w']['code'] = $this->toppollgd("w","I",$date);
		$data['III']['w']['code'] = $this->toppollgd("w","III",$date);

		$tdata = file_get_contents(config('services.JSON_DIR')."site/team.json");
		$json = json_decode($tdata,true);
	
		foreach ($json as $gender => $rec) {
			foreach ($rec as $div => $team) {
				$tm[$team['code']] = $team['shortname'];			
			}
		}

                $data['I']['m']['hl'] = "Men's D-I";
                $data['III']['m']['hl'] = "Men's D-III";
                $data['I']['w']['hl'] = "Women's D-I";
                $data['III']['w']['hl'] = "Women's D-III";

                $data['I']['m']['link'] = "d-i-mens-poll";
                $data['III']['m']['link'] = "d-iii-mens-poll";
                $data['I']['w']['link'] = "d-i-womens-poll";
                $data['III']['w']['link'] = "d-iii-womens-poll";

		$data['I']['m']['shortname'] = $tm[$data['I']['m']['code']];
                $data['III']['m']['shortname'] = $tm[$data['III']['m']['code']];
                $data['I']['w']['shortname'] = $tm[$data['I']['w']['code']];
                $data['III']['w']['shortname'] = $tm[$data['III']['w']['code']];

		$html = view("json/toppollnew", ['data' => $data])->render();
		return Response::json(array('html' => $html)); # "", 'json' => $data));
	}

	public function toppollgd($gender,$division,$date){
		# poll is all in one file
		$json_obj = $this->poll_data();
                $polldate = $json_obj->poll_data->$gender->$division->$date->PollDate;
		$pd = $json_obj->poll_data->$gender->$division->$polldate;

		return $pd->teams[0];	
	}

	public function rpi($division,$full_gender)
	{
		# composite is all in one file
		$gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
		$filename = $this->get_jsonfilename("ranking","rpi",$gender,$division,$season,"sc");
		//echo $filename;
		
		$data = [];
		$html = "";
		$meta_data = [];
		$datatable['dt1']['sub_title'] = "";
		
		$page_title = $this->get_full_gender($gender)."'s Division ".$division." Ratings Percentage Index";
	
                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate Ratings Percentage Index";
                }
	
		if (file_exists($filename)) {	
			$stat = stat($filename);
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$meta_data = $json_obj->meta_data;	
               
    		$data['dt1'] = $json_obj;
    		$datatable['dt1']['sub_title'] = "";
    		return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}

	public function rpi2($division,$full_gender)
        {
                # composite is all in one file
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
                $filename = $this->get_jsonfilename("ranking","rpi2",$gender,$division,$season,"sc");
                //echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." Ratings Percentage Index";

                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate Ratings Percentage Index";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;

                $data['dt1'] = $json_obj;
                $datatable['dt1']['sub_title'] = "";
                return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
        }

	public function npi($division,$full_gender)
        {
                # composite is all in one file
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
                $filename = $this->get_jsonfilename("ranking","npi",$gender,$division,$season,"sc");
                //echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." NCAA Percentage Index";

                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate NCAA Percentage Index";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;

                $data['dt1'] = $json_obj;
                $datatable['dt1']['sub_title'] = "";
                return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
        }

	public function npi2($division,$full_gender)
        {
                # composite is all in one file
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
                $filename = $this->get_jsonfilename("ranking","npi2",$gender,$division,$season,"sc");
                //echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." NCAA Percentage Index";

                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate NCAA Percentage Index";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;

                $data['dt1'] = $json_obj;
                $datatable['dt1']['sub_title'] = "";
                return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}

	public function pwrnpi($division,$full_gender)
        {
                # composite is all in one file
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
                $filename = $this->get_jsonfilename("ranking","pwrnpi",$gender,$division,$season,"sc");
                //echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." Collegiate PairWise Rankings";

                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate PairWise Rankings";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;

                $data['dt1'] = $json_obj;
                $datatable['dt1']['sub_title'] = "";
                return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
        }

	public function rpiraw($division,$full_gender)
        {
                # composite is all in one file
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);
                $filename = $this->get_jsonfilename("ranking","rpiraw",$gender,$division,$season,"sc");
                //echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." Ratings Percentage Index";

                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate Ratings Percentage Index";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;

                $data['dt1'] = $json_obj;
                $datatable['dt1']['sub_title'] = "";
                return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
        }

	public function pairwise_rankings($division,$full_gender)
	{
		$gender = $gender = $this->get_gender($full_gender);
		$division = strtoupper($division);
		$season = $this->get_season("");
		$full_season = substr($season,0,4)."-".substr($season,-4);

		# composite is all in one file
		$season = $this->get_season($full_season);
		$filename = $this->get_jsonfilename("ranking","pwr",$gender,$division,$season,"sc");
		#echo $filename;
		
		$data = [];
		$html = "";
		$meta_data = [];
		$datatable['dt1']['sub_title'] = "";
		
		$page_title = $this->get_full_gender($gender)."'s Division ".$division." PairWise Rankings";
		if ($division == "I" && $gender == "w") {
			$page_title = "Women's National Collegiate PairWise Rankings";
		}
		
		if (file_exists($filename)) {	
			$stat = stat($filename);
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$meta_data = $json_obj->meta_data;
			if (method_exists($json_obj,"clean_data")) {$json_obj->clean_data = "";}

    			$data['dt1'] = $json_obj;
    			$datatable['dt1']['sub_title'] = "";
    			return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}

	public function pairwise_rankings2($division,$full_gender)
        {
                $gender = $gender = $this->get_gender($full_gender);
                $division = strtoupper($division);
                $season = $this->get_season("");
                $full_season = substr($season,0,4)."-".substr($season,-4);

                # composite is all in one file
                $season = $this->get_season($full_season);
                $filename = $this->get_jsonfilename("ranking","pwr2",$gender,$division,$season,"sc");
                #echo $filename;

                $data = [];
                $html = "";
                $meta_data = [];
                $datatable['dt1']['sub_title'] = "";

                $page_title = $this->get_full_gender($gender)."'s Division ".$division." PairWise Rankings";
                if ($division == "I" && $gender == "w") {
                        $page_title = "Women's National Collegiate PairWise Rankings";
                }

                if (file_exists($filename)) {
                        $stat = stat($filename);
                        $json = file_get_contents($filename);
                        $json_obj = json_decode($json);
                        $meta_data = $json_obj->meta_data;
                        if (method_exists($json_obj,"clean_data")) {$json_obj->clean_data = "";}

                        $data['dt1'] = $json_obj;
                        $datatable['dt1']['sub_title'] = "";
                        return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title, 'updated' => date("F d, Y, h:i:s A",$stat['mtime'])));
                }
                return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}

	public function krach($gender,$division,$full_season,$full_gender)
	{
		# composite is all in one file
		$season = $this->get_season($full_season);
		$filename = $this->get_jsonfilename("ranking","krach",$gender,$division,$season,"sc");
		// echo $filename;
		
		$data = [];
		$html = "";
		$meta_data = [];
		$datatable['dt1']['sub_title'] = "";
		
		$page_title = $this->get_full_gender($gender)."'s Division ".$division." KRACH: ".$full_season;
		
		if (file_exists($filename)) {	
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$meta_data = $json_obj->meta_data;	
    		$data['dt1'] = $json_obj;
    		$datatable['dt1']['sub_title'] = "";
    		return Response::json(array('json' => $data, 'datatable' => $datatable, 'page_title' => $page_title));
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));
	}		
}
