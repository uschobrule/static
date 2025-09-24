<div id="sum_players">
<b>{{$teams->teamInfo->$V->shortname}}</b>

<table class="display compact nowrap" cellspacing="0" width="100%" id="vboxstats">
	<thead>
		<tr>
			<th>Player</th>
			<th>G</th>
			<th>A</th>    
			<th>PTS</th>
			<th>SH Net/SH Tot</th>
			<th>G%</th>
			<th>+/-</th>
			<th>Blocks</th>
			<th>FO W/FO Tot</th>
		</tr>
	</thead>
	<tbody>
@php
	$tot = ['g' => 0, 'a' => 0, 'pts' => 0, 'shotsNet' => 0, 'shots' => 0, 'plsmns' => 0, 'bl' => 0, 'foW' => 0, 'fo' => 0];
@endphp

@foreach ($boxstats->$V as $rec)
@if($rec->name != "") 
@php
	$tot['g'] += $rec->g;
	$tot['a'] += $rec->a;
	$tot['pts'] += $rec->pts;
	$tot['shotsNet'] += $rec->shotsNet;
	$tot['shots'] += $rec->shots;
	$tot['plsmns'] += $rec->plsmns;
	$tot['bl'] += $rec->bl;
	$tot['foW'] += $rec->foW;
	$tot['fo'] += $rec->fo;
@endphp
		<tr>
                        <td>{{$rec->name}}</td>
			<td>{{$rec->g}}</td>
			<td>{{$rec->a}}</td>
			<td>{{$rec->pts}}</td>
			<td>{{$rec->shotsNet}}/{{$rec->shots}}</td>
@if($rec->shots > 0)
			<td>{{round(100 * $rec->g / $rec->shots, 2)}}%</td>
@else
                        <td>0.00%</td>
@endif
			<td>{{$rec->plsmns}}</td>
			<td>{{$rec->bl}}</td>
			<td>{{$rec->foW}}/{{$rec->fo}}</td>
		</tr>
@endif
@endforeach
	</tbody>
<tfoot>
<tr>
	<th>Total</th>
        <th>{{$tot['g']}}</th>
        <th>{{$tot['a']}}</th>
        <th>{{$tot['pts']}}</th>
        <th>{{$tot['shotsNet']}}/{{$tot['shots']}}</th>

@if($tot['shots'] > 0)
        <th>{{round(100 * $tot['g'] / $tot['shots'], 2)}}%</th>
@else
        <th>0.00%</th>
@endif

        <th>{{$tot['plsmns']}}</th>
        <th>{{$tot['bl']}}</th>
        <th>{{$tot['foW']}}/{{$tot['fo']}}</th>
</tr>
</tfoot>
</table>

<b>{{$teams->teamInfo->$H->shortname}}</b>
<table class="display compact nowrap" cellspacing="0" width="100%" id="hboxstats">
        <thead>
		<tr>
                        <th>Player</th>
                        <th>G</th>
                        <th>A</th>
			<th>PTS</th>
                        <th>SH Net/SH Tot</th>
                        <th>G%</th>
                        <th>+/-</th>
                        <th>Blocks</th>
                        <th>FO W/FO Tot</th>
                </tr>
        </thead>
        <tbody>
@php
        $tot = ['g' => 0, 'a' => 0, 'pts' => 0, 'shotsNet' => 0, 'shots' => 0, 'plsmns' => 0, 'bl' => 0, 'foW' => 0, 'fo' => 0];
@endphp
	
@foreach ($boxstats->$H as $rec)
@if($rec->name != "")
@php
        $tot['g'] += $rec->g;
        $tot['a'] += $rec->a;
        $tot['pts'] += $rec->pts;
        $tot['shotsNet'] += $rec->shotsNet;
        $tot['shots'] += $rec->shots;
        $tot['plsmns'] += $rec->plsmns;
        $tot['bl'] += $rec->bl;
        $tot['foW'] += $rec->foW;
        $tot['fo'] += $rec->fo;
@endphp

		<tr>
                        <td>{{$rec->name}}</td>
                        <td>{{$rec->g}}</td>
                        <td>{{$rec->a}}</td>
                        <td>{{$rec->pts}}</td>
                        <td>{{$rec->shotsNet}}/{{$rec->shots}}</td>
@if($rec->shots > 0) 
                        <td>{{round(100 * $rec->g / $rec->shots, 2)}}%</td>
@else
                        <td>0.00%</td>
@endif
                        <td>{{$rec->plsmns}}</td>
                        <td>{{$rec->bl}}</td>
                        <td>{{$rec->foW}}/{{$rec->fo}}</td>
                </tr>
@endif
@endforeach
	</tbody>
<tfoot>
<tr>
        <th>Total</th>
        <th>{{$tot['g']}}</th>
        <th>{{$tot['a']}}</th>
        <th>{{$tot['pts']}}</th>
        <th>{{$tot['shotsNet']}}/{{$tot['shots']}}</th>

@if($tot['shots'] > 0)
        <th>{{round(100 * $tot['g'] / $tot['shots'], 2)}}%</th>
@else
        <th>0.00%</th>
@endif

        <th>{{$tot['plsmns']}}</th>
        <th>{{$tot['bl']}}</th>
        <th>{{$tot['foW']}}/{{$tot['fo']}}</th>
</tr>
</tfoot>
</table>

<div class="p-3">
<b>G = Goals</b><br>
<b>A = Assists</b><br>
<b>PTS = Total Points</b><br>
<b>SH Net = Shots On Net</b><br>
<b>SH TOT = Total Shots Attempted</b><br>
<b>G% = Scoring percentage per shot attempted</b><br>
<b>+/- = Plus Minus</b><br>
<b>Blocks = Shots blocked by the player</b><br>
<b>FO W = Faceoff Wins</b><br>
<b>FO TOT = Faceoff Attempts</b><br> 
</div>
</div>
