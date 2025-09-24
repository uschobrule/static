<div class='scroll_wrapper'>
@if (array_key_exists("cn",$data))
<h2>{{$data['cn']}}</h2>

<div id='weekly_pickem_wrapper' class='divTable'>
<div id='u_scoreboard_home' class='headRow' style='margin-left: 0px;'>

<div class='divCell'>Rank</div>
<div class='div1stCell divCell'>Username</div>
<div class='divCell'>Points</div>

@foreach ($data['g'] as $d)

<div id='box_{{$d->game_id}}' class='u_home_scores divCell' data-parameters='{{$d->game_id}}'>

<div id='score_{{$d->game_id}}' class='u_gameday_score divCell'>
<div id='vscore_{{$d->game_id}}' class='u_vscore u_score live_vscore'>
{{$d->vis_score}}
</div>
<div id='hscore_{{$d->game_id}}' class='u_hscore u_score live_hscore'>
{{$d->home_score}}
</div>
</div>

<div id='vis_{{$d->game_id}}' class='u_gameday_row u_gameday_vis'>
<div class='u_team_icon' style='background: url("https://json.uscho.com/images/logos/{{$d->visitor}}.gif"); background-size:16px auto; background-repeat: no-repeat;background-position: center;'></div>
<div class='u_team_name'>
{{strtoupper($d->chs_visitor)}}
</div>
</div>

<div id='home_{{$d->game_id}}' class='u_gameday_row u_gameday_home'>
<div class='u_team_icon' style='background: url("https://json.uscho.com/images/logos/{{$d->home}}.gif"); background-size:16px auto; background-repeat: no-repeat;background-position: center;'></div>
<div class='u_team_name'>
{{strtoupper($d->chs_home)}}
</div>
</div>

<div id='info_{{$d->game_id}}' class='u_gameday_info_front'>

<div id='time_{{$d->game_id}}' class='u_time_front u_info_front live_mins'>
@if ($d->vis_score == "") 
{{$d->starttime}}
@elseif ($d->vscore == "")
{{$d->pd_time}} {{$d->pd}}
@endif
</div>

<div id='pd_{{$d->game_id}}' class='live_pd u_final_front u_pd_front'>
@if ($d->vscore != "") 
F
@endif
@if ($d->ots == 1)
OT
@elseif ($d->ots > 1)
{{$d->ots}}OT
@endif
</div>

</div>

</div>
@endforeach
</div>

@php
ksort($data['rnk']);
@endphp

@foreach ($data['rnk'] as $rnk => $uv)
@php
$u = $uv['username'];
@endphp

<div class='divRow'>
<div class='divCell'>
{{$uv['rnk']}}
</div>
<div class='div1stCell divCell'>
{{$u}}
</div>
<div class='divCell'>
{{$data['res'][$u]['cur']}}
</div>

@php
ksort($data['u'][$u]);
@endphp
@foreach ($data['u'][$u] as $ind => $g)
<div class='{{$g->row_class}} divCell'>
{{strtoupper($g->win_team_code_pick)}}
</div>
@endforeach
</div>
@endforeach

</div>
@else 
No data is available at this time.
@endif
</div>
