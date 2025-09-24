<?php

namespace App\Http\Controllers;
use Response;

class TopPerformers extends JSONController
{	
	public function top_performers($gender,$division,$season) 
	{
		$data = $this->data($gender,$division,$season);
//		return Response::json(array('data' => $data));

		$html = "";
		if ($data) {
			$html = view("json/top_performers", ['data' => $data])->render();
		}
		return Response::json(array('html' => $html));
	}
	public function data($gender,$division,$season){
		//echo "$gender,$division,$season";
		$con = $this->_init_db();

		$full_season = substr($season,0,4)."-".substr($season,4,4);
		$dg = ['m' => ['I' => 'division-i-men','III' => 'division-iii-men','II' => 'division-iii-men'], 'w' => ['I' => 'division-i-women','III' => 'division-iii-women','II' => 'division-iii-women']];

		$sql = "
SELECT
'".$gender."' gender,
g.hdiv,
CONCAT('/stats/player/".$gender."id,',p.id,'/',p.first,'-',p.last) path,
CONCAT(p.first,' ',p.last,', ',UPPER(s.chCode)) name,
SUM(t.g) g,
SUM(t.a) a,
SUM(t.g + t.a) pts
FROM gd".$season.".".$gender."games g
JOIN gd".$season.".".$gender."summary_p t ON t.game_ID = g.rowid
JOIN db_perpetual.".$gender."players p ON t.player_ID = p.ID
JOIN db_perpetual.".$gender."schoolCurrent s ON s.school_ID = t.school_ID
WHERE g.gdate >= DATE_FORMAT(DATE_SUB(now(),INTERVAL 7 DAY),'%Y%m%d')
AND g.hdiv = '".$division."'
GROUP BY g.hdiv,name
HAVING pts > 2
ORDER BY pts DESC, g DESC
LIMIT 5";

		//echo $sql;

                $res=mysqli_query($con,$sql);
		$json = [];

		if(mysqli_num_rows($res)>0) {
			while($row=mysqli_fetch_object($res)) {
       				$json[] = $row;
   			}
		}

		$sqlg = "SELECT
'".$gender."' gender,
g.hdiv,
CONCAT('/stats/player/".$gender."id,',p.id,'/',p.first,'-',p.last) path,
CONCAT(p.first,' ',p.last,', ',UPPER(s.chCode)) name,
SUM(t.ga) ga,
SUM(t.saves) saves,
SUM(t.seconds) seconds,
SUM(t.sho) sho,
SUM(if(t.decision = 'W',1,0)) w,
SUM(if(t.decision = 'L',1,0)) l,
SUM(if(t.decision = 'T',1,0)) t,
1-SUM(t.ga) / SUM(t.saves+t.ga) svp
FROM gd".$season.".".$gender."games g
JOIN gd".$season.".".$gender."summary_g t ON t.game_ID = g.rowid
JOIN db_perpetual.".$gender."players p ON t.player_ID = p.ID
JOIN db_perpetual.".$gender."schoolCurrent s ON s.school_ID = t.school_ID
WHERE g.gdate >= DATE_FORMAT(DATE_SUB(now(),INTERVAL 7 DAY),'%Y%m%d')
AND g.hdiv = '".$division."'
GROUP BY g.hdiv,name
HAVING seconds > 3600
ORDER BY svp DESC, w DESC
LIMIT 5";

		//echo $sqlg;

		$resg=mysqli_query($con,$sqlg);
                $jsong = [];

                if(mysqli_num_rows($resg)>0) {
			while($row=mysqli_fetch_object($resg)) {
				$gaa = 0.00;
        			if ($row->seconds > 0) {
                			$gaa = $row->ga/$row->seconds * 3600;
				}
				$row->gaa = $gaa;

                                $jsong[] = $row;
                        }
                }

		return ['scoring' => $json, 'goaltending' => $jsong];
	}
}
