@php
$limit = 4;
$ind = 0;
$week = $data['info']['max_week'];
$cuser = "";
$cuser_login = "";
$user_rnk = "";

if ($user_id != '') {
	if (array_key_exists($user_id,$data['user'])) {
		$cuser = $data['user'][$user_id]['week'][$week];
		$user_rnk = $cuser['rnk'];
		$cuser_login = $data['user'][$user_id]['user_login'];
	}
	if ($user_rnk > 4) {
		$limit = 3;
	}
}
$culogin = substr($cuser_login,0,12);
if (strlen($cuser_login) > 12) { 
	$culogin = $culogin."**";
}
@endphp
<aside class='widget widget_links'>
<div style="width:300px;height:300px">
<div class='gallerywp-widget-title block-title td-block-title' style="width:300px;margin-bottom:0px;backg round:#9A3334;font: normal normal 15px Open Sans,Arial,Helvetica,sans-serif;co lor: #ffffff;text-transform: uppercase;text-align: left;"><span>Pickem Leaders</span></div>
<div style="width:300px;height:197px;">
<div style="float:left;width:150px;height:197px;border-right:1px;border-left:1px;border-color:black;">
<table style="font-size:.8em;">
<tr><th>Week {{$week}}</th><th>Pct</th></tr>
@foreach ($data['res'][$week] as $rec)
@php
        $weekly = $data['user'][$rec]['week'][$week];
        $user_login = $data['user'][$rec]['user_login'];
	$ulogin = substr($user_login,0,12);
	if (strlen($user_login) > 12) { 
		$ulogin = $ulogin."**";
	}
@endphp
<tr><td style="padding:.3em;">{{$weekly['rnk']}} <a href="https://social.uscho.com/user/{{$user_login}}" style="color:#a71e40;font-weight:bold;">{{$ulogin}}</a></td><td>{{round(100*$weekly['cur_per'],1)}}%</td></tr>
@php
if (++$ind == $limit) break;
@endphp
@endforeach
<tr><td>...</td><td></td></tr>
@php
        if ($user_rnk > 4) {
@endphp
<tr><td style="padding:.3em;">{{$cuser['rnk']}} <a href='https://social.uscho.com/user/{{$cuser_login}}' style='color:#a71e40;font-weight:bold;'>{{$culogin}}</a></td><td>{{round(100*$cuser['cur_per'],1)}}%</td></tr>
@php
        }
@endphp
<tr><td colspan=2><a href="https://social.uscho.com/weekly-pickem-leaders/{{$week}}/" style="color:#a71e40;font-weight:bold;">Weekly Results</a></td></tr>
</table>
</div>
<div style="float:left;width:150px;height:197px;border-right:1px;border-color:black;">
<table style="font-size:.8em;">
<tr><th>Season</th><th>Pct</th></tr>
@php
$ind = 0;
$limit = 4;
if ($user_id != '') {
        $user_rnk = "";
        if (array_key_exists($user_id,$data['user'])) {
		$cuser = $data['user'][$user_id]['week']['season'];
                $user_rnk = $cuser['rnk'];
		$cuser_login = $data['user'][$user_id]['user_login'];
        }
        if ($user_rnk > 4) {
                $limit = 3;
        }
}
@endphp
@foreach ($data['res']['season'] as $rec)
@php
	$season = $data['user'][$rec]['week']['season'];
	$user_login = $data['user'][$rec]['user_login'];
        $ulogin = substr($user_login,0,12);
        if (strlen($user_login) > 12) {
                $ulogin = $ulogin."**";
        }
@endphp
<tr><td style="padding:.3em;">{{$season['rnk']}} <a href='https://social.uscho.com/user/{{$cuser_login}}' style='color:#a71e40;font-weight:bold;'>{{$ulogin}}</a></td><td>{{round(100*$season['cur_per'],1)}}%</td>
@php
if (++$ind == $limit) break;
@endphp
@endforeach
<tr><td>...</td><td></td></tr>
@php
        if ($user_rnk > 4) {
@endphp
<tr><td>{{$cuser['rnk']}} <a href='https://social.uscho.com/user/{{$cuser_login}}' style='color:#a71e40;font-weight:bold;'>{{$culogin}}</a></td><td>{{round(100*$cuser['cur_per'],1)}}%</td></tr>
@php
        }
@endphp
<tr><td colspan=2><a href="https://social.uscho.com/weekly-pickem-leaders/" style="color:#a71e40;font-weight:bold;">Season Results</a></td></tr>
</table>
</div>
</div>
<div style="background-color:black;color:white;font-weight:bold;font-size:.8em;width:300px;height:50px;">
<center>Compete for prizes or just for the fun of it at<br> 
<a href="https://social.uscho.com/weekly-pickem" style="color:red;font-weight:bold;">social.USCHO.com</a></center>
</div>
</div>
</aside>
