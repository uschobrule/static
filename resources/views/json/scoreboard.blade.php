<table class="pure-table" id="test">
	<thead>
		<tr>
			<th>Conference|Overall|Home|Away</th>
		</tr>
	</thead>
	<tbody>
@foreach ($json->data as $date => $gd)
<td>{{$date}}</td>
@foreach ($gd as $conf => $gdc)
<td>{{$conf}}</td>
@foreach ($gdc as $rec)
		<tr>
			<td>{{$rec->starttime}}</td>
			<td>{{$rec->vis_name}}</td>
			<td>{{$rec->vscore}}</td>
			<td>@ {{$rec->home_name}}</td>
			<td>{{$rec->hscore}}</td>
			<td>{{$rec->arena_name}}</td>
		</tr>
@endforeach
@endforeach
@endforeach
	</tbody>	
</table>