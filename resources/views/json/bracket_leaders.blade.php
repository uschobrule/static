@php
$limit = 4;
$ind = 0;
$user_rnk = $crnk;

if ($user_rnk > 4) {
	$limit = 3;
}
@endphp
<aside class='widget widget_links'>
<div style="width:300px;height:300px">
<div class='gallerywp-widget-title' style="width:300px;margin-bottom:0px;backg round:#9A3334;font: normal normal 15px Open Sans,Arial,Helvetica,sans-serif;co lor: #ffffff;text-transform: uppercase;text-align: left;"><span>{{ $header }} Bracket Leaders</span></div>
<div style="width:300px;height:197px;">
<table style="font-size:.8em;">
<tr><th>Rank</th><th>User</th><th>Pts</th><th>Tiebreaker</th><th>{{$tbh2}}</th></tr>
@foreach ($data as $rec)
<tr><td style="padding:.3em;">{{$rec[0]}}</td><td>{!!$rec[1]!!}</td><td>{{$rec[2]}}</td><td>{{$rec[3]}}</td><td>{{$rec[4]}}</td></tr>
@php
if (++$ind == $limit) break;
@endphp
@endforeach
<tr><td>...</td><td></td></tr>
@php
        if ($user_rnk > 4) {
@endphp
<tr><td style="padding:.3em;">{{$crnk}}</td><td><a href='{{$lnk}}' style='color:#a71e40;font-weight:bold;'>{{$cuser_login}}</a></td><td>{{$cpts}}</td><td>{{$ctiebreaker}}</td><td>{{$ctiebreaker2}}</td></tr>
@php
        }
@endphp
<tr><td colspan=2><a href="https://social.uscho.com{{$leaders}}" style="color:#a71e40;font-weight:bold;">Results</a></td></tr>
</table>
</div>
<div style="background-color:black;color:white;font-weight:bold;width:300px;height:50px;">
<center>Compete for prizes or just for the fun of it at<br> 
<a href="https://social.uscho.com/weekly-pickem" style="color:red;font-weight:bold;">social.USCHO.com</a></center>
</div>
</div>
</aside>
