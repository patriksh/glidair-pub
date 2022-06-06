<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
  margin-bottom: 48px;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
</style>

<table>
    <tr>
        <th>Poredak</th>
        <th>Name</th>
        <th>Club</th>
        <th>Female</th>
        <th>State</th>
        <th>Score</th>
        @for($i = 1; $i <= $competition->rounds; $i++)
            <th>{{ $i }}. round</th>
        @endfor
    </tr>
    @foreach ($results as $participant)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $participant->user->name }}</td>
            <td>{{ $participant->user->club ? $participant->user->club->name : '' }}</td>
            <td>{{ $participant->user->gender == 'F' ? '1': '0' }}</td>
            <td>{{ \App\Helpers\CountryCodes::alpha2To3($participant->user->country) }}</td>
            <td>{{ $participant->score }}</td>
            @foreach($participant->rounds as $round)
                <th>
                    @if($round->ignore)
                        <s>{{ $round->score }}</s>
                    @else
                        {{ $round->score }}
                    @endif
                </th>
            @endforeach
        </tr>
    @endforeach
</table>