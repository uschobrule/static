<div id="topperformers" class="container">
<div class="row">
<div class="col-12 col-md-5 m-0 p-0">
<h3>Scoring</h3>
<table class="table table-sm table-striped table-bordered table-hover table-responsive-sm text-nowrap">
<tr>
<th>Name</th>
<th class="w-100 text-right">G-A-PTS</th>
</tr>
@foreach($data['scoring'] as $ind => $rec)
<tr>
<td><a href="{{$rec->path}}/">{{$rec->name}}</a></td>
<td class="w-100 text-right">{{$rec->g}}-{{$rec->a}}-{{$rec->pts}}</td>
</tr>
@endforeach
</table>
</div>
<div class="col-12 col-md-6 m-0 ml-md-4 p-0">
<h3>Goaltending</h3>
<table class="table table-sm table-striped table-bordered table-hover table-responsive-sm text-nowrap">
<tr>
<th>Name</th>
<th>W-L-T (SHO)</th>
<th>SVP/GAA</th>
</tr>
@foreach($data['goaltending'] as $ind => $rec)
<tr>
<td><a href="{{$rec->path}}/">{{$rec->name}}</a></td>
<td>{{$rec->w}}-{{$rec->l}}-{{$rec->t}} ({{$rec->sho}})</td>
<td>{{number_format($rec->svp,3)}}/{{number_format($rec->gaa,2)}}</td>
</tr>
@endforeach
</table>

</div>
</div>
</div>
