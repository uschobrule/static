<table class="pure-table">
	<thead>
		<tr>
			<th>No.</th>
			<th>Name</th>
			<th>Pos</th>
			<th>Yr</th>
			<th>Ht</th>
			<th>Wt</th>
			<th>Home Town</th>			
			<th>Last Team</th>		
		</tr>
	</thead>
	<tbody>
@foreach ($json as $rec)
		<tr>
			<td>{{$rec->num}}</td>
			<td>{{$rec->first}} {{$rec->last}}
@if ($rec->iscaptain)
(C)
@elseif ($rec->isasst_captain)
(A)
@endif 			
			</td>
			<td>{{$rec->pos}}</td>
			<td>{{$rec->yr}}</td>
			<td>{{$rec->ht}}</td>
			<td>{{$rec->wt}}</td>
			<td>{{$rec->hometown}}, {{$rec->homeprov}}</td>
			<td>{{$rec->lastteam}}</td>
		</tr>
@endforeach
	</tbody>	
</table>
