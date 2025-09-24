<div id="lineshead">
<h3>Visitor</h3>
<table class="display compact nowrap" cellspacing="0" width="100%" id="linestablev">
	<thead>
		<tr>
			<th>Line Num</th>
			<th>Player 1</th>
			<th>Player 2</th>
			<th>Player 3</th>
		</tr>
	</thead>
	<tbody>

@foreach ($lineup->V->lines as $line)
<tr><td>F{{$line->number}}</td><td>LW: {{$line->lw}}</td><td>C: {{$line->c}}</td><td>RW: {{$line->rw}}</td></tr>
@endforeach
@foreach ($lineup->V->lines as $line)
<tr><td>D{{$line->number}}</td><td>LD: {{$line->ld}}</td><td>RD: {{$line->rd}}</td><td>G: {{$line->g}}</td></tr>
@endforeach

	</tbody>
</table>

<h3>Home</h3>
<table class="display compact nowrap" cellspacing="0" width="100%" id="linestableh">
        <thead>
                <tr>
			<th>Line Num</th>
                        <th>Player 1</th>
                        <th>Player 2</th>
                        <th>Player 3</th>
                </tr>
        </thead>
        <tbody>
@foreach ($lineup->H->lines as $line)
<tr><td>F{{$line->number}}</td><td>LW: {{$line->lw}}</td><td>C: {{$line->c}}</td><td>RW: {{$line->rw}}</td></tr>
@endforeach
@foreach ($lineup->H->lines as $line)
@if($line->number < 4)
<tr><td>D{{$line->number}}</td><td>LD: {{$line->ld}}</td><td>RD: {{$line->rd}}</td>
@if(property_exists($line,"XS"))
<td>{{$line->XS->pos}}: {{$line->XS->name}}</td>
@else
<td>G: {{$line->g}}</td>
@endif
</tr>
@endif
@endforeach
        </tbody>
</table>
</div>
