<?php

namespace App\Http\Controllers\Traits;

date_default_timezone_set('America/Chicago');

trait TimeData
{
    public function get_current_season()
    { 
    
      $full_season = $this->get_current_full_season();
      $season = preg_replace( "/-/", "", $full_season );
  
      return $season;
    } 

    public function get_current_full_season()
    { 
    
      $year = date("Y");
      $month = date("m");
    
      $season = $year."-".($year+1);
      if ($month >= 1 && $month < 7) {
        $season = ($year-1)."-".$year;
      }

      return $season;
    }

    public function season_to_fullseason($season)
    {
      return substr($season,0, 4)."-".substr($season,4, 4);
    }

    public function gdate_to_fulldate($gdate)
    {
      return substr($gdate,0, 4)."-".substr($gdate,4, 2)."-".substr($gdate,6, 2);
    }

    public function current_gdate() {
      // show yesterdays data through 11 AM
      //return 20240302;
      return date("Ymd", strtotime('-17 hours'));
    }

    public function get_seasons() {
      $seasons = [];

      $startyear = 1998;
      $currentyear = date("Y");
      $month = date("m");

      $years = $currentyear-$startyear+1;

      if ($month < 7) {$years--;}

      for ($ind = 0; $ind < $years; $ind++) {
        array_push($seasons,['id' => $ind, 'text' => ($startyear+$ind)."-".($startyear+$ind+1)]);
      }

      rsort($seasons);
      return $seasons;
    }
}
