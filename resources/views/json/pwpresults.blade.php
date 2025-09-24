<div class="sharelink">Shareable link for your picks: <a href="https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}">https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}</a></div>
<table class="pwp">
<tr><th class='pwpRank'>Rank</th><th>Team</th><th class='pwpRec'>Record</th><th class='pwpPts'>Pts</th><th>RPI</th></tr>
@foreach ($data['pwrdata'] as $ind => $rec)
@php 
	$class = "";
	if ($rec['ncaa'] == 1) {$class = 'pwpncaa';};
        if (array_key_exists($rec['team_name'],$data['chmp'])) {$class = 'pwpchamp';};
	$fudge = "";
	if($rec['fudge']) {$fudge = "*";}
	if(($rec['pts']=="")) {$rec['pts']=0;}
@endphp
<tr class='{{$class}}'>
<td class='pwpRank'>{{$rec['rnk']}}</td>
<td class='pwpTeam'>{{$rec['team_name']}}</div></td>
<td class='pwpRec'>{{$rec['record']}}</div></td>
<td class='pwpPts'>{{$rec['pts']}}</div></td>
<td class='pwpRPI'>{{number_format($rec['adjrpiqwb'],4,'.' , ' ').$fudge}}</td></tr>
@endforeach
</table>

<p><i>*Team's RPI has been adjusted to remove negative effect from defeating weak opponent.</i><p>
<div class="sharelink">Shareable link for your picks: <a href="https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}">https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}</a></div>

<h2>Your Picks</h2>
<table>
<tr><th>Game</th><th>Pick</th></tr>
@foreach ($data['picks'] as $code => $rec)
<tr><td>{{$rec['gdesc']}}</td><td>{{$rec['team']}}</td></tr>
@endforeach
</table>

<div class="sharelink">Shareable link for your picks: <a href="https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}">https://social.uscho.com/college-hockey-pairwise-predictor/?pwpID={{$data['uniqueID']}}</a></div>
<!-- 
<div class="pwcbuttons"><b>Share your results:</b>
<div class="socialthis_button"><div class="SocialThis" style="padding-top:5px;"><a href="https://www.facebook.com/" onclick="windowpop("https://www.facebook.com/sharer/sharer.php?u=";, 500, 500,'{{$data['uniqueID']}}'); return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnShareFacebook57x20.png" width="57" height="20" alt="Share on Facebook" title="Share on Facebook"></a><span class="social-botton"><a href="https://twitter.com/" onclick="windowpop("https://twitter.com/intent/tweet?via=USCHO&amp;url=", 500, 500,'{{$data['uniqueID']}}');return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnTweet57x20.png" width="57" height="20" alt="Tweet" title="Share on Twitter"></a></span><span class="social-botton"><a href="https://plus.google.com/" onclick="windowpop("https://plus.google.com/share?url=", 500, 500,'{{$data['uniqueID']}}'); return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnShareGooglePlus57x20.png" width="57" height="20" alt="Share on Google+" title="Share on Google+"></a></span><span class="social-botton"></span></div></div><b>Unique link to this scenario: <a href="http://social.uscho.com/pairwise-predictor/?ui={{$data['uniqueID']}}" target="_blank" class="pwplink">https://social.uscho.com/pairwise-predictor/?ui={{$data['uniqueID']}}</a></b>
 -->
</div>
