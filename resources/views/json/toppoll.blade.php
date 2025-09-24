<div class="wpb_wrapper row" style="margin-left: 16px;width: 323px;">
<div><h4 class="block-title hl_box" style="margin-left: 5px;"><span>USCHO.com poll leaders</span></h4>
<div>
@foreach ($data as $div => $d2)
@foreach ($d2 as $gender => $rec)
<div class='polls_team'>
<div class="row">
<div class='column'><h4 class="polls_cat">{!!$rec['hl']!!}</h4></div>
<div class='polls_logo column'><img width ="75" src="//static-cdn.uscho.com/images/logos/{{$rec['code']}}.gif" alt="{{$rec['shortname']}}" /></div>
</div>
<h4 class='polls_numone'>{{$rec['shortname']}}</h4><a href="/rankings/{{$rec['link']}}/">Poll &raquo;</a>
</div>
@endforeach
@endforeach
</div>
</div>
</div>
