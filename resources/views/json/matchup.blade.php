@php
	$home_team = $data['home_team'];
	$vis_team = $data['vis_team'];

	$hpoll = "NA";
	$vpoll = "NA";

	if(array_key_exists($home_team,$data['poll'])) {$hpoll = $data['poll'][$home_team];}
	if(array_key_exists($vis_team,$data['poll'])) {$vpoll = $data['poll'][$vis_team];}

	$bt = "<b>";
	$bte = "</b>";
	$btn = "";
        $btne = "";

	if($data['neutral'] == "yes") {
		$bt = "";
        	$bte = "";
		$btn = "<b>";
        	$btne = "</b>";
	}
@endphp
<div style="width:auto;height:auto;">
<div class='gallerywp-widget-title'><span><font color="red"><b>Matchup Info</b></font></span></div>
<div>
<div style="float:left;width:auto;height:210px;border:1px;border-style:solid;border-color:black;padding:5px;">
<div style="white-space: nowrap;"><b>Team</b><br>&nbsp;</div>
<div style="white-space: nowrap;">Record (Pct)</div>
<div style="white-space: nowrap;">Home Rec (Pct)</div>
<div style="white-space: nowrap;">Road Rec (Pct)</div>
<div style="white-space: nowrap;">Neut Rec (Pct)</div>
<div style="white-space: nowrap;">USCHO Poll</div>
<div style="white-space: nowrap;">PWR Rnk</div>
<div style="white-space: nowrap;">RPI (Rnk)</div>
<div style="white-space: nowrap;">SOS (SOS Rnk)</div>
<div style="white-space: nowrap;">H2H</div>
<div style="white-space: nowrap;">Common Opp</div>
</div>
<div style="float:left;width:auto;height:210px;border:0px;border-top:1px;border-bottom:1px;border-style:solid;border-color:black;padding:5px;">
<div style="white-space: nowrap;"><b>{{$home_team}}<br>(Home)</b></div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team]['record']}} ({{number_format($data['pwr'][$home_team]['winpct'],4, '.' , ' ')}})</div>
<div style="white-space: nowrap;">{!!$bt!!}{{$data['rpi'][$home_team]->home->rec}} ({{number_format($data['rpi'][$home_team]->home->winpct,4, '.' , ' ')}}){!!$bte!!}</div>
<div style="white-space: nowrap;">{{$data['rpi'][$home_team]->road->rec}} ({{number_format($data['rpi'][$home_team]->road->winpct,4, '.' , ' ')}})</div>
<div style="white-space: nowrap;">{!!$btn!!}{{$data['rpi'][$home_team]->neut->rec}} ({{number_format($data['rpi'][$home_team]->neut->winpct,4, '.' , ' ')}}){!!$btne!!}</div>
<div style="white-space: nowrap;">{{$hpoll}}</div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team]['rnk']}}</div>
<div style="white-space: nowrap;">{{number_format($data['pwr'][$home_team]['adjrpiqwb'],4, '.' , ' ')}} ({{$data['pwr'][$home_team]['rpirnk']}})</div>
<div style="white-space: nowrap;">{{number_format($data['rpi'][$home_team]->sos,4, '.' , ' ')}} ({{$data['rpi'][$home_team]->sosrnk}})</div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team][$vis_team]->h2h->t1}}</div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team][$vis_team]->cop->t1}}</div>
</div>
<div style="float:left;width:auto;height:210px;border:1px;border-style:solid;border-color:black;padding:5px;">
<div style="white-space: nowrap;"><b>{{$vis_team}}<br>(Visitor)</b></div>
<div style="white-space: nowrap;">{{$data['pwr'][$vis_team]['record']}} ({{number_format($data['pwr'][$vis_team]['winpct'],4, '.' , ' ')}})</div>
<div style="white-space: nowrap;">{{$data['rpi'][$vis_team]->home->rec}} ({{number_format($data['rpi'][$vis_team]->home->winpct,4, '.' , ' ')}})</div>
<div style="white-space: nowrap;">{!!$bt!!}{{$data['rpi'][$vis_team]->road->rec}} ({{number_format($data['rpi'][$vis_team]->road->winpct,4, '.' , ' ')}}){!!$bte!!}</div>
<div style="white-space: nowrap;">{!!$btn!!}{{$data['rpi'][$vis_team]->neut->rec}} ({{number_format($data['rpi'][$vis_team]->neut->winpct,4, '.' , ' ')}}){!!$btne!!}</div>
<div style="white-space: nowrap;">{{$vpoll}}</div>
<div style="white-space: nowrap;">{{$data['pwr'][$vis_team]['rnk']}}</div>
<div style="white-space: nowrap;">{{number_format($data['pwr'][$vis_team]['adjrpiqwb'],4, '.' , ' ')}} ({{$data['pwr'][$vis_team]['rpirnk']}})</div>
<div style="white-space: nowrap;">{{number_format($data['rpi'][$vis_team]->sos,4, '.' , ' ')}} ({{$data['rpi'][$vis_team]->sosrnk}})</div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team][$vis_team]->h2h->t2}}</div>
<div style="white-space: nowrap;">{{$data['pwr'][$home_team][$vis_team]->cop->t2}}</div>
</div>
</div>
</div>
