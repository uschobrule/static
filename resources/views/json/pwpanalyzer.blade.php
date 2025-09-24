<h2>Pairwise Predictor Analyzer</h2>

<div><b>Note:</b> We treat each result as equally likely.</div>

<h2>Predictor Grid</th>
<table class="pwp">
<tr>
<th>Team/Seed</th>
<th>Avg Seed</th>
<th>In NCAA (Per)</th>
@for($i=1;$i<=16;$i++)
<th>{{$i}}</th>
@endfor
</tr>
@foreach($data['tot'] as $tc => $res)
@php
        $class = "pwpbubble";
        if ($res['cnt'] == $data['total']) {$class = 'pwpncaa';};
        if (array_key_exists($res['team_code'],$data['needconfwin'])) {$class = 'pwpmustwin';};
	if (array_key_exists($res['team_code'],$data['gres']['champs'])) {$class = 'pwpchamp';};
@endphp
<tr class="{{$class}}">
<td><div class='{{$class}}'>{{$res['team']}}</div></td>
<td><div class='{{$class}}'>{{number_format($res['wgt'],2,"."," ")}}</div></td>
<td><div class='{{$class}}'>{{$res['cnt']}} ({{number_format(100*$res['cnt']/$data['total'],2,"."," ")}})</div></td>
@for($i=1;$i<=16;$i++)
@php
	$cnt = 0;
	if (array_key_exists($i,$data['team'][$res['team_code']])) {
		$cnt = $data['team'][$res['team_code']][$i];
	}
@endphp
<td><div class='{{$class}}'>{{$cnt}}</div></td>
@endfor
@endforeach
</tr>
</table>

<h2>Games not yet final</h2>
@foreach($data['active_games'] as $gc => $res)
<b>{{$cdd[$gc]['gdesc']}} ({{$res['gt']}}) - Possible Outcomes:</b>
@foreach($res['outcomes'] as $tc => $cnt)
{{strtoupper($tc)}}
@endforeach
<br>
@endforeach
