<div id="boxhead">
<h3>{{$json->header->game_result}}</h3>

{{$json->header->game_type}} Game<br>
{{$json->header->game_date}} at {{$json->header->arena_name}} (Attendance: {{$json->header->attendance}})<br>

@if ($json->header->sho_notes)
<br>
{{$json->header->sho_notes}}
@endif
</div>

<table class="display compact nowrap" cellspacing="0" width="100%" id="boxsummary">
	<thead>
		<tr>
			<th></th>
			<th colspan={{count($json->per) + 1}}>Scoring</th>
			<th colspan={{count($json->per) + 1}}>Shots</th>
			<th></th>
			<th></th>
		</tr>
		<tr>
			<th>Team</th>
@foreach ($json->per as $per)
			<th>{{$per}}</th>
@endforeach
			<th></th>
@foreach ($json->per as $per)
			<th>{{$per}}</th>
@endforeach
			<th></th>
			<th>Penalties</th>		
			<th>Power Plays</th>	
		</tr>
	</thead>
	<tbody>

@foreach ($json->summary as $rec)

@if (property_exists($rec,"shortname"))
		<tr>
			<td>{{$rec->chs_team_code}}</td>
@foreach ($json->per as $per)
                        <td>
@if (isset($rec->goals->$per))
             {{$rec->goals->$per}}
@else
				0
@endif         
						</td>  
@endforeach

			<td class="text-nowrap">= {{$rec->tgoals}}</td>	
@foreach ($json->per as $per)
                        <td>
@if (isset($rec->shots->$per))
             {{$rec->shots->$per}}
@endif
			</td>  
@endforeach
			<td class="text-nowrap">= {{$rec->tshots}}</td>	
			<td>{{$rec->tpen}}-{{$rec->tpim}}</td>
			<td>{{$rec->tppg}}-{{$rec->tppo}}</td>
		</tr>
@endif
@endforeach
	</tbody>	
</table>

<br>
<h4>Goals</h4>

<table class="display compact nowrap" cellspacing="0" width="100%" id="boxgoals">
	<thead>
		<tr>
			<th>Per</th>
			<th>Team</th>
			<th>Scorer</th>
			<th>Assist 1</th>
			<th>Assist 2</th>
			<th>Goal Type</th>	
			<th>Time</th>		
		</tr>
	</thead>
	<tbody>
@foreach ($json->goals as $rec)
		<tr>
			<td>{{$rec->period}}</td>
			<td class="text-nowrap">{{$rec->goalcode}}</td>
			<td>{{$rec->scorer}}</td>
			<td>{{$rec->assist1}}</td>
			<td>{{$rec->assist2}}</td>
			<td>
@if ($rec->isgwg)
GWG
@endif
@if ($rec->isshg)
SHG
@endif 	
@if ($rec->isppg)
PPG
@endif
@if ($rec->iseng)
ENG
@endif
			{{$rec->goal_type}}
			</td>
			<td>{{$rec->time}}</td>
		</tr>
@endforeach
	</tbody>	
</table>

<br>
<h4>Penalties</h4>

<table class="display compact nowrap" cellspacing="0" width="100%" id="boxpen">
	<thead>
		<tr>
			<th>Per</th>
			<th>Team</th>
			<th>Player</th>
			<th>Min</th>	
			<th>Infraction</th>
			<th>Time</th>		
		</tr>
	</thead>
	<tbody>
@foreach ($json->penalties as $rec)
		<tr>
			<td>{{$rec->period}}</td>
			<td class="text-nowrap">{{$rec->pencode}}</td>
			<td>{{$rec->name}}</td>
			<td>{{$rec->pims}}</td>
			<td>{{$rec->infraction}}</td>
			<td>{{$rec->time}}</td>
		</tr>
@endforeach
	</tbody>	
</table>

<br>
<h4>Goaltending</h4>

<table class="display compact nowrap" cellspacing="0" width="100%" id="boxgoalie">
	<thead>
		<tr>
			<th></th>
			<th></th>
			<th colspan={{count($json->per) + 1}}>Saves</th>
			<th></th>
		</tr>	
		<tr>
			<th>Team</th>
			<th>Goalie</th>
@foreach ($json->per as $per)
			<th>{{$per}}</th>
@endforeach
			<th></th>
			<th>Goals Allowed</th>		
		</tr>
	</thead>
	<tbody>
@foreach ($json->goalies as $rec)
                <tr>
                        <td class="text-nowrap">{{$rec->goaliecode}}</td>
                        <td>{{$rec->name}} ({{$rec->time}} {{$rec->decision}})</td>
                  
@foreach ($json->per as $per)
                        <td>
@if (isset($rec->$per))
             {{$rec->$per}}
@endif         
						</td>  
@endforeach
                        <td>= {{$rec->tsaves}}</td>
                        <td>({{$rec->tga}} GA)</td>
                </tr>
@endforeach

	</tbody>	
</table>

<br>
<h4>Player Scoring Summary</h4>

<table class="display compact nowrap" cellspacing="0" width="100%" id="teamscore">
	<thead>
		<tr>
			<th>Team</th>
			<th>Player</th>
			<th>Scoring</th>	
		</tr>
	</thead>
	<tbody>
@foreach ($json->teamscore as $rec)
                <tr>
                        <td>{{$rec->chs_team_code}}</td>
                        <td>{{$rec->name}}</td>
                        <td>{{$rec->g}}-{{$rec->a}} = {{$rec->pts}}</td>
                </tr>
@endforeach
	</tbody>	
</table>

<br>
<h4>Player Penalty Summary</h4>

<table class="display compact nowrap" cellspacing="0" width="100%" id="teampen">
	<thead>
		<tr>
			<th>Team</th>
			<th>Player</th>
			<th>Penalties</th>	
		</tr>
	</thead>
	<tbody>
@foreach ($json->teampen as $rec)
                <tr>
                        <td>{{$rec->chs_team_code}}</td>
                        <td>{{$rec->name}}</td>
                        <td>{{$rec->pen}}-{{$rec->pims}}</td>
                </tr>
@endforeach
	</tbody>	
</table>

<div id="boxfoot">
<b>Referee(s): {{$json->header->ref}}<br>
Asst. Referee(s): {{$json->header->aref}}</b>
</div>

<br>

<div id="boxdisclaimer">
Box scores by USCHO.com — U.S. College Hockey Online are compiled from official game box scores. Changes may have been made to the official box score after it was released to the media. Consequently, USCHO's box scores may disagree with reports published by other sources.
</div>
