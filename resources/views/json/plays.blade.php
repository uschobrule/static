<div id="playshead">
<table class="display compact nowrap" cellspacing="0" width="100%" id="playstable">
	<thead>
		<tr>
			<th>Period</th>
			<th>Number</th>
			<th>Play</th>
		</tr>
	</thead>
	<tbody>

@foreach ($plays as $key => $play)
<tr><td>P{{$play->period}}</td><td>{{$play->number}}</td><td>{{$play->text}}</td></tr>
@endforeach
	</tbody>
</table>
</div>
