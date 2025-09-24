<h2>Your PairWise Comparisons</h2>
<table>
<tr><th>Rank</th><th>Team</th><th>Record</th><th>Pts</th><th>RPI</th></tr>
@foreach ($data['teams'] as $ind => $rec)
@php 
	$champ = "";
	if (array_key_exists($rec['name'],$data['chmp'])) {$champ = 'background-color: #9FC;';};
	$fudge = "";
	if($rec['fudge']) {$fudge = "*";}
@endphp
<tr><td style='{{$champ}}'>{{$rec['rk']}}</td><td style='{{$champ}}'>{{$rec['name']}}</td><td>{{$rec['record']}}</td><td>{{$rec['pwrk']}}</td><td>{{number_format($rec['adjrpiqwb'],4,'.' , ' ').$fudge}}</td></tr>
@endforeach
</table>

<p>* RPI adjusted for wins that lower with a win.<p>

<h2>Your Picks</h2>
<table>
<tr><th>Game</th><th>Pick</th></tr>
@foreach ($data['picks'] as $code => $rec)
<tr><td>{{$rec['gdesc']}}</td><td>{{$rec['team']}}</td></tr>
@endforeach
</table>

<div class="pwcbuttons"><b>Share your results:</b>
<div class="socialthis_button"><div class="SocialThis" style="padding-top:5px;"><a href="https://www.facebook.com/" onclick="windowpop(&quot;https://www.facebook.com/sharer/sharer.php?u=&quot;, 500, 500,'pwp_5c95a4306ae7e'); return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnShareFacebook57x20.png" width="57" height="20" alt="Share on Facebook" title="Share on Facebook"></a><span class="social-botton"><a href="https://twitter.com/" onclick="windowpop(&quot;https://twitter.com/intent/tweet?via=USCHO&amp;url=&quot;, 500, 500,'pwp_5c95a4306ae7e');return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnTweet57x20.png" width="57" height="20" alt="Tweet" title="Share on Twitter"></a></span><span class="social-botton"><a href="https://plus.google.com/" onclick="windowpop(&quot;https://plus.google.com/share?url=&quot;, 500, 500,'pwp_5c95a4306ae7e'); return false;" style="padding-right:15px;"><img src="http://static.uscho.com/images//btnShareGooglePlus57x20.png" width="57" height="20" alt="Share on Google+" title="Share on Google+"></a></span><span class="social-botton"></span></div></div><b>Unique link to this scenario: <a href="http://pwp.uscho.com/rankings/pairwise-predictor/?uniq=pwp_5c95a4306ae7e" target="_blank" class="pwplink">https://social.uscho.com//pairwise-predictor/?ui={{$data['uniqueID']}}</a></b>
</div>
<div class="pwcbuttons"><button class="pwpUpdate updatepwc" onclick="update();">Update Brackets</button><button class="pwpUpdate startover" onclick="reset();">Start Over</button><span class="loading"></span></div>
