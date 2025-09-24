<?php

namespace App\Http\Controllers;
use Response;

class RosterController extends JSONController
{	
	public function parseuri($parts)
	{	
		// $parts = explode("/",$uri);
		$count =  count($parts);

		$template = "roster";
		$full_season = $this->get_current_full_season();
		$gender = $this->get_gender($parts[1]);
		$code = $parts[0];
		$sitecode = $this->get_sitecode($gender,$code);
		if ($count > 2) $full_season = $parts[2];
		$division = $sitecode['division'];		
		$code = $sitecode['code'];
		
		$season = $this->get_season($full_season);
		
		return array($template,$code,$gender,$division,$season,$sitecode,$full_season);
	}
	public function get_page_title($type,$code,$gender,$division,$season,$sitecode,$full_season)
	{
		$full_season = substr($season,0,4)."-".substr($season,4,4);
		
		$shortname = "";
		$desc = "Roster";
		if (array_key_exists("shortname",$sitecode)) {$shortname = $sitecode['shortname']." ";}
	    return $shortname.$this->get_full_gender($gender)."'s Hockey ".$full_season." ".$desc;
	}	
	public function roster($code,$full_gender)
        {
		return $this->roster_season($code,$full_gender,"");
	}
	public function roster_season($code,$full_gender,$full_season = "")
	{
		# parse_uri
		//list ($template,$code,$gender,$division,$season,$sitecode,$full_season) = $this->parseuri($uri);

		$gender = $this->get_gender($full_gender);
		$sitecode = $this->get_sitecode($gender,$code);
		$division = $sitecode['division'];
                $code = $sitecode['code'];
		$template = "roster";

		if ($full_season == "") {$full_season = $this->get_current_full_season();}
		$season = $this->get_season($full_season);

		# load conf
		$page_title = $this->get_page_title($template,$code,$gender,$division,$season,$sitecode,$full_season);
		
		# composite is all in one file
		$filename = $this->get_jsonfilename("roster",$code,$gender,$division,$season,$sitecode);

		if (file_exists($filename)) {				
			$json = file_get_contents($filename);
			$json_obj = json_decode($json);
			$templatec = str_replace("-","_",$template);

			if (property_exists($json_obj,"data")) {
				list ($data,$datatable,$meta_data,$page_title) = $this->roster_format($template,$json_obj,$code,$page_title,$gender,$division,$full_season);
				return Response::json(array('json' => $data, 'meta_data' => $meta_data, 'datatable' => $datatable, 'page_title' => $page_title));
			}
		}
		return Response::json(array('html' => "Currently not available", 'json' => "", 'page_title' => $page_title, 'datatable' => []));		
	}
	public function roster_format($template,$json,$code,$page_title,$gender,$division,$full_season){

		$resdata['data'] = [];
		foreach ($json->data AS $orec) {
			$rec = [];
			array_push($rec,$orec[0]);
			if (count($orec) > 8) {
				array_push($rec,"<a href='".$orec[8]."'>".$orec[1]."</a>");
			} else {
				array_push($rec,$orec[1]);
			}
			array_push($rec,$orec[2]);
			array_push($rec,$orec[3]);
			array_push($rec,$orec[4]);
			array_push($rec,$orec[5]);
			array_push($rec,$orec[6]);
			array_push($rec,$orec[7]);
			array_push($resdata['data'],$rec);
		}

		$datatable['dt1']['sub_title'] = '';
		$data['dt1']['data'] = $resdata;
		$columns = [];
		foreach ($json->meta_data->columns as $ind => $col) {
			$col->sortable = true;
			array_push($columns,$col);
		}

		$json->meta_data->columns = $columns;

		$data['dt1']['meta_data'] = $json->meta_data;
		$data['dt1']['sub_title'] = "";
		
		return array($data,$datatable,$json->meta_data,$page_title);	
	}	
}
