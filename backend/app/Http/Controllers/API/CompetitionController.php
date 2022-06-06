<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Round;
use App\Models\Club;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;
use Shuchkin\SimpleXLSX;
use ZipArchive;
use League\ISO3166\ISO3166;

class CompetitionController extends Controller
{
    // Pronađi sva natjecanja.
    public function index()
    {
        $competitions = Competition::with(
            'judges',
            'participants',
            'participants.user:id,club_id,name,country',
            'participants.user.club:id,name',
            'participants.rounds'
        )->orderBy('created_at', 'DESC')->get();
    
        return response()->json($competitions);
    }

    // Pronađi natjecanje sa svim podacima (suci, natjecatelji, rezultati) po ID-u.
    public function show($id)
    {
        $competition = Competition::with('judges', 'participants', 'participants.user', 'participants.user.club', 'participants.rounds')->find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        return response()->json($competition);
    }

    // Unesi novo natjecanje.
    public function store(Request $request)
    {
        // Provjeri ulazne podatke, ime, lokacija, broj serija i datum su obavezni.
        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|image',
            'name' => 'required',
            'location' => 'required',
            'rounds' => 'required|integer',
            'rounds_ignored' => 'required|integer',
            'date' => 'required|date'
        ]);

        if($validator->fails()) {
            return response()->json(['message' => 'Neispravni podaci.'], 400);
        }

        $input = [
            'name' => $request->name,
            'location' => $request->location,
            'rounds' => $request->rounds,
            'rounds_ignored' => $request->rounds_ignored,
            'date' => $request->date
        ];

        // Spremi logo, ako postoji.
        if($request->file('logo')) {
            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $competition = Competition::create($input);

        return response()->json(['status' => 1, 'id' => $competition->id]);
    }

    // Uredi natjecanje.
    public function update(Request $request, Competition $competition)
    {
        $input = $request->all();

        // Ako postoji novi logo, izbriši stari logo i spremi novi.
        if($request->file('logo')) {
            if($competition->logo) {
                Storage::delete('logo/' . $competition->logo);
            }

            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $roundsIgnored = $competition->rounds_ignored;

        $competition->update($input);

        // Ako je promijenjen broj serija koje se ne boduju označi ih u bazi i ponovo izračunaj broj bodova za svakog natjecatelja.
        if($request->exists('rounds_ignored') && $request->rounds_ignored != $roundsIgnored) {
            $this->flagIgnoredRounds($competition);
            $this->updateTotalScore($competition);
        }

        return response()->json(['status' => 1]);
    }

    // Izbriši natjecanje.
    public function destroy(Competition $competition)
    {
        // Izbriši generirane izvještaje za natjecanje.
        Storage::deleteDirectory('reports/' . $competition->id);

        // Izbriši logo natjecanja.
        Storage::delete('logo/' . $competition->logo);

        $competition->delete();

        return response()->json(['status' => 1]);
    }

    // Unesi suce.
    public function updateJudges(int $id, Request $request)
    {
        $competition = Competition::find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        // Spremi direktora.
        if($request->director) {
            $competition->update(['director' => $request->director]);
        }

        // Ukloni sve suce koji imaju prazno ime ili funkciju.
        $judges = $request->judges;
        foreach($judges as $key => $judge) {
            if(!$judge['name'] || !$judge['role']) unset($judges[$key]);
        }

        // Ako ima ispravnih sudaca, izbriši stare suce i unesi nove.
        if(count($judges)) {
            $competition->judges()->delete();
            $competition->judges()->createMany($judges);
        }

        return response()->json(['status' => 1]);
    }

    // Unesi natjecatelje.
    public function updateParticipants(int $id, Request $request)
    {
        $competition = Competition::with('participants')->find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        $participants = $request->input('participants');
        $wereUpdated = [];

        // Dodaj natjecatelje (ili ih ažuriraj ako već postoje), prati ID svakog natjecatelja koji je dodan/ažuriran.
        foreach($participants as $participant) {
            $competition->participants()->updateOrCreate(['user_id' => $participant['user_id']], $participant);
            $wereUpdated[] = $participant['user_id'];
        }

        // Izbriši sve natjecatelje koji nisu bili dodani/ažurirani, znači da ih je korisnik uklonio na frontendu.
        foreach($competition->participants as $participant) {
            if(!in_array($participant->user_id, $wereUpdated)) {
                $competition->participants()->where('user_id', $participant->user_id)->delete();
            }
        }

        return response()->json(['status' => 1]);
    }

    // Unesi natjecatelje iz Airtribune XLS tablice.
    public function updateParticipantsFromXls(int $id, Request $request)
    {
        $competition = Competition::with('participants')->find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        // Provjeri je li ulazna datoteka xls/xlsx.
        $file = $request->file('file');
        if(!$file || !in_array($file->extension(), ['xls', 'xlsx'])) {
            return response()->json(['message' => 'Nema datoteke ili je u nevaljanom formatu.'], 400);
        }

        // Provjeri je li ulazna datoteka ispravna xls/xlsx datoteka.
        $xlsx = SimpleXLSX::parse($file->getPathName());
        if(!$xlsx) {
            return response()->json(['message' => 'Neispravna datoteka.'], 400);
        }

        $rows = $xlsx->rows();

        // Pronađi indekse svih stupaca koji su nam potrebni.
        $i = ['Participant number' => null, 'Name' => null, 'Surname' => null, 'Gender' => null, 'Fai nation' => null, 'Club' => null, 'Team' => null];
        $iKeys = array_keys($i);
        foreach($rows[0] as $c => $cell) {
            if(in_array($cell, $iKeys)) {
                $i[$cell] = $c;
            }
        }

        // Provjeri jesu li svi stupci pronađeni, ako ne tablica je neispravna.
        $missing = [];
        foreach($i as $column => $key) {
            if(!$key) $missing[] = $column;
        }

        if(count($missing)) {
            return response()->json(['message' => 'Neispravna tablica, neka polja nedostaju (' . implode(', ', $missing) . ').'], 400);
        }

        // Počni s unosom natjecatelja iz tablice.
        $order = 1;
        foreach($rows as $r => $row) {
            // Preskoči prvi redak jer on sadrži imena stupaca.
            if($r === 0) continue;

            // Pronađi korisnika s jedinstevnim Airtribune identifikatorom ("Participant number"), ako ne postoji stvori ga.
            $user = User::where('airtribune_id', $row[$i['Participant number']])->first();
            if(!$user) {
                // Pronađi klub s navedenim imenom, ako ime nije prazno ili "/" i klub ne postoji stvori ga.
                $club_name = trim($row[$i['Club']]);
                if(!empty($club_name) && $club_name != '/') {
                    $club = Club::where('name', $club_name)->first();
                    if(!$club) {
                        $club = Club::create(['name' => $club_name]);
                    }
                }

                $user = User::create([
                    'club_id' => isset($club) ? $club->id : null,
                    'name' => ucwords(strtolower($row[$i['Name']] . ' ' . $row[$i['Surname']])), // Ime + prezime, svaka riječ počinje velikim slovom.
                    'gender' => $row[$i['Gender']][0],
                    'country' => (new ISO3166)->alpha3($row[$i['Fai nation']])['alpha2'], // Pretvori troznamenkasti kod države u troznamenkasti.
                    'airtribune_id' => $row[$i['Participant number']]
                ]);
            }

            // Pronađi tim s navedenim imenom, ako ime nije prazno ili "/" i tim ne postoji stvori ga.
            $team_name = trim($row[$i['Team']]);
            if(!empty($team_name) && $team_name != '/') {
                $team = $competition->teams->where('name', $team_name)->first();
                if(!$team) {
                    $competition->teams()->create(['name' => $team_name]);
                }
            }

            // Klub, korisnik i tim postoje, unesi natjecatelja.
            $competition->participants()->updateOrCreate(['user_id' => $user->id], [
                'user_id' => $user->id,
                'team_id' => isset($team) ? $team->id : null,
                'order' => $order
            ]);

            $order++;
        }

        return response()->json(['status' => 1]);
    }

    // Unesi rezultate.
    public function updateRounds(int $id, Request $request)
    {
        $competition = Competition::with('participants')->find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        // Izbriši postojeće rezultate.
        $existingParticipants = array_column($competition->participants->toArray(), 'id');
        Round::whereIn('participant_id', $existingParticipants)->delete();

        // Spremi rezultate.
        foreach($request->rounds as $participant => $rounds) {
            foreach($rounds as $round => $score) {
                if($score === null) continue;

                Round::create([
                    'round' => $round,
                    'score' => $score,
                    'participant_id' => $participant
                ]);
            }
        }

        // Označi serije koje se ne boduju u bazi i izračunaj broj bodova za svakog natjecatelja.
        $this->flagIgnoredRounds($competition);
        $this->updateTotalScore($competition);

        return response()->json(['status' => 1]);
    }

    // Preuzmi izvještaj za natjecanje.
    public function downloadReport(int $id)
    {
        $competition = Competition::with('judges', 'participants', 'participants.user', 'participants.user.club', 'participants.rounds')->find($id);
        if(!$competition) {
            return response()->json(['message' => 'Natjecanje nije pronađeno.'], 404);
        }

        // Stvori zip datoteku.
        $zipPath = storage_path('app/reports/' . $competition->id . '/report.zip');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Definiraj filtere za svaku tablicu izvještaja.
        $reports = [
            'overall' => [],
            'overall_women' => ['user.gender' => 'F'],
            'overall_croatia' => ['user.country' => 'HR'],
            'overall_women_croatia' => ['user.gender' => 'F', 'user.country' => 'HR']
        ];

        // Stvori izvještaj i dodaj ga u zip datoteku.
        foreach($reports as $name => $conditions) {
            $fileName = $name . '.html';
            Storage::put('reports/' . $competition->id . '/' . $fileName, $this->generateGeneralReport($competition, $conditions));
            $zip->addFile(storage_path('app/reports/' . $competition->id . '/' . $fileName), $fileName);
        }

        $zip->close();

        $downloadName = Str::slug($competition->name, '-') . '.zip';

        return response()->download($zipPath, $downloadName);
    }

    // Spremi logo natjecanja s nasumičnim imenom.
    private function storeLogo($file)
    {
        $filename = uniqid() . md5($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        $file->storePubliclyAs('logo', $filename);

        return $filename;
    }

    // Označi serije koje se ne boduju.
    private function flagIgnoredRounds($competition)
    {
        // Prođi kroz svakog natjecatelja koji ima unesene rezultate.
        foreach($competition->participants as $participant) {
            $rounds = Round::where('participant_id', $participant->id)->get()->toArray();
            
            if(count($rounds)) {
                // Reseteriaj svaku seriju.
                Round::where('participant_id', $participant->id)->update(['ignore' => false]);

                // Sortiraj serije od one s najvećim bodova do one s najmanjim bodova.
                $roundsSortedByScore = $rounds;
                usort($roundsSortedByScore, function($a, $b) {
                    return $b['score'] - $a['score'];
                });
    
                // Označi X (X = broj serija koje se ne boduju) najgorih serija (najgora = najviši broj bodova).
                for($i = 0; $i < $competition->rounds_ignored; $i++) {
                    $roundId = $roundsSortedByScore[$i]['id'];
                    Round::where('id', $roundId)->update(['ignore' => true]);
                }
            }
        }
    }

    // Izračunaj broj bodova za svakog natjecatelja.
    private function updateTotalScore($competition)
    {
        // Prođi kroz svakog natjecatelja.
        foreach($competition->participants as $participant) {
            $score = 0;
            $rounds = Round::where('participant_id', $participant->id)->get();

            // Pribroji bodove ako se serija boduje.
            foreach($rounds as $round) {
                if(!$round->ignore) $score += $round->score;
            }

            $participant->update(['score' => $score]);
        }
    }

    // Stvori tablicu za izvješće prema HTML predlošku (resources/views/reports/general.blade.php).
    private function generateGeneralReport($competition, $conditions = array())
    {
        $participants = $competition->participants;

        foreach($conditions as $column => $value) {
            $participants = $participants->where($column, $value);
        }

        $results = $participants->sortBy(function($participant) {
            return $participant->score;
        });

        $report = view('reports/general', ['competition' => $competition, 'results' => $results])->render();

        return $report;
    }

    // Pomoćne funkcije za testiranje aplikacije, unose rezultate iz ručno stvorenih tablica s bodovima.

    /*
    public function importExistingResults()
    {
        $competition = Competition::with('participants', 'participants.user')->find(11);
        $existingParticipants = array_column($competition->participants->toArray(), 'id');
        
        Round::whereIn('participant_id', $existingParticipants)->delete();

        $xlsx = SimpleXLSX::parse('drzavno2021.xlsx');

        foreach($xlsx->rows() as $r => $row) {
            if($r === 0) continue;

            $participant = $competition->participants->where('user.name', $row[2])->first();
            if(!$participant) continue;

            for($i = 1; $i <= $competition->rounds; $i++) {
                Round::create([
                    'round' => $i,
                    'score' => $row[6 + $i],
                    'participant_id' => $participant->id
                ]);
            }
        }

        $this->flagIgnoredRounds($competition);
        $this->updateTotalScore($competition);
    }
    */

    /*
    public function importEverythingFromResultTables()
    {
        $xlsx = SimpleXLSX::parse('drzavno2020.xlsx');

        $competition = Competition::create([
            'name' => 'Državno 2020',
            'location' => 'Hrvatska',
            'rounds' => 6,
            'rounds_ignored' => 1,
            'date' => '2020-01-01 00:00:00'
        ]);

        $rows = $xlsx->rows();

        foreach($rows as $r => $row) {
            if($r === 0) continue;

            try {
                (new ISO3166)->alpha3(str_replace(['SLO', 'CRO'], ['SVN', 'HRV'], trim($row[5])))['alpha2'];
            } catch(Exception $e) {
                echo $e->getMessage();
                die;
            }

            // only for 2014 2015
            // $name = trim(mb_ucwords(mb_strtolower(implode(' ', array_reverse(explode(' ', $row[2]))))));
            $name = trim(mb_ucwords(mb_strtolower($row[2])));

            // for 2019 all index + 1

            if(empty($name)) continue;

            $user = User::where('name', $name)->first();
            if(!$user) {
                $club_name = trim($row[3]);
                if(!empty($club_name) && $club_name != '/' && $club_name != '-') {
                    $club = Club::where('name', $club_name)->first();
                    if(!$club) {
                        $club = Club::create(['name' => $club_name]);
                    }
                }

                $user = User::create([
                    'club_id' => isset($club) ? $club->id : null,
                    'name' => $name,
                    'gender' => 'M',
                    'country' => (new ISO3166)->alpha3(str_replace(['SLO', 'CRO'], ['SVN', 'HRV'], trim($row[5])))['alpha2']
                ]);
            }

            $participant = $competition->participants()->updateOrCreate(['user_id' => $user->id], [
                'user_id' => $user->id,
                'team_id' => null,
                'order' => $row[0]
            ]);

            for($i = 1; $i <= $competition->rounds; $i++) {
                Round::create([
                    'round' => $i,
                    'score' => $row[6 + $i],
                    'participant_id' => $participant->id
                ]);
            }
        }

        $this->flagIgnoredRounds($competition);
        $this->updateTotalScore($competition);
    }
    */
}
