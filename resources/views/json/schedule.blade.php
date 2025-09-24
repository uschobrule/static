<table class="pure-table" id="test">
	<thead>
		<tr>
			<th>Conference|Overall|Home|Away</th>
@foreach ($json->rec_type as $rt)
			<th>{{strtoupper($rt)}}</th>
@endforeach
			<th>Win %</th>
			<th>Pts</th>
			<th>GF-GA</th>
			<th>W</th>
			<th>L</th>
			<th>T</th>
			<th>Win %</th>
			<th>GF-GA</th>	
			<th>W</th>
			<th>L</th>
			<th>T</th>
			<th>W</th>
			<th>L</th>
			<th>T</th>		
		</tr>
	</thead>
	<tbody>
@foreach ($json->team as $team => $rec)
		<tr>
			<td>{{$rec->shortname}}</td>
@foreach ($json->rec_type as $rt)
			<td>{{$rec->conf->$rt}}</td>
@endforeach
			<td>{{$rec->conf->winpct}}</td>
			<td>{{$rec->conf->pts}}</td>
			<td>{{$rec->conf->gf}}-{{$rec->conf->ga}}</td>
			<td>{{$rec->tot->w}}</td>
			<td>{{$rec->tot->l}}</td>
			<td>{{$rec->tot->t}}</td>
			<td>{{$rec->tot->winpct}}</td>
			<td>{{$rec->tot->gf}}-{{$rec->tot->ga}}</td>
			<td>{{$rec->home->w}}</td>
			<td>{{$rec->home->l}}</td>
			<td>{{$rec->home->t}}</td>
			<td>{{$rec->away->w}}</td>
			<td>{{$rec->away->l}}</td>
			<td>{{$rec->away->t}}</td>
		</tr>
@endforeach
	</tbody>	
</table>