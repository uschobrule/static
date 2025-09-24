@foreach ($data as $div => $d2)
@foreach ($d2 as $gender => $rec)
<div class='polls_team'>
<div class="row">
<div class='column'><h4 class="polls_cat">{!!$rec['hl']!!}</h4></div>
<div class='polls_logo column'><img width ="70" src="/images/logos/{{$rec['code']}}.gif" alt="{{$rec['shortname']}}" /></div>
</div>
<h4 class='polls_numone'>{{$rec['shortname']}}</h4><a href="/rankings/{{$rec['link']}}/">Poll &raquo;</a>
</div>
@endforeach
@endforeach

