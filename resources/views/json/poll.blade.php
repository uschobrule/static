<h1 id="u_page_title">{{$page_title}}</h1>

<table class="display compact nowrap" cellspacing="0" width="100%" id="poll{{$ind}}">
	<thead>
		<tr>
			<th>Rnk</th>
			<th>Team</th>
			<th>(First Place Votes)</th>
			<th>Record</th>
			<th>Points</th>
			<th>Last Poll</th>	
		</tr>
	</thead>
	<tbody>
@foreach ($json->data as $rec)
		<tr>
			<td>{{$rec->rnk}}</td>
			<td><a href="{{$rec->link}}">{{$rec->shortname}}</a></td>
			<td>
@if ($rec->first_pv)
	({{$rec->first_pv}})
@endif
			</td>
			<td>{{$rec->record}}</td>
			<td>{{$rec->pts}}</td>
			<td>{{$rec->prev_rnk}}</td>
		</tr>
@endforeach
	</tbody>	
</table>
@if ($json->other)
Others receiving votes: {{$json->other}}
@endif

<div id="polldate">
<select id="polldate" name="polldate" onchange="window.location.replace(this.options[this.selectedIndex].value);">
@foreach ($pd as $date => $full_date)
<option value="/rankings/{{$template}}/{{$date}}">{{$full_date}}</option>
@endforeach
</select>
</div>

<script>
jQuery(document).ready(function($){
    $('#poll{{$ind}}').DataTable( {
        scrollX:        true,
        responsive: false,
        scrollCollapse: true,
        paging:         false,
        bFilter: false,
        bInfo : false,
        fixedColumns:   {
            leftColumns: 2
        }
    } );
} );
</script>
