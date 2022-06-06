<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Competition;
use App\Models\Participant;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    // Poredak najboljih natjecatelja.
    public function participants(Request $request)
    {
        $year = (int) $request->year ? (int) $request->year : 2021; // date('Y');

        $results = [];

        // Pronađi natjecanja "u tekućoj sezoni".
        $competitions = Competition::whereDate('date', '>=', $year . '-01-01')->get();
        $competitionIds = array_column($competitions->toArray(), 'id');

        // Pronađi natjecatelje, njihove korisnike, serije i natjecanja.
        $participants = Participant::whereIn('competition_id', $competitionIds)->with('user', 'rounds')->get();

        foreach($participants as $participant) {
            if(!count($participant->rounds)) continue;

            if(isset($results[$participant->user->id]['score'])) {
                $results[$participant->user->id]['score'] += $participant->score;
            } else {
                $results[$participant->user->id]['score'] = $participant->score;
            }

            $results[$participant->user->id]['user'] = $participant->user;

            $competition = $competitions->find($participant->competition_id)->toArray();
            $competition['participant']['rounds'] = $participant->rounds;
            $results[$participant->user->id]['competitions'][] = $competition;
        }

        // Sortiraj po ukupnim bodovima.
        usort($results, function($a, $b) {
            return $a['score'] - $b['score'];
        });

        return response()->json($results);
    }

    // Poredak najboljih klubova.
    public function clubs(Request $request)
    {
        $year = (int) $request->year ? (int) $request->year : 2021; // date('Y');

        $results = [];

        // Pronađi natjecanja "u tekućoj sezoni".
        $competitions = Competition::whereDate('date', '>=', $year . '-01-01')->get();
        $competitionIds = array_column($competitions->toArray(), 'id');
        
        // Pronađi natjecatelje, njihove korisnike, serije i natjecanja.
        $participants = Participant::whereIn('competition_id', $competitionIds)->with('user', 'user.club', 'rounds')->get();

        foreach($participants as $participant) {
            if(!$participant->user->club) continue;
            if(!count($participant->rounds)) continue;

            if(isset($results[$participant->user->club->id]['score'])) {
                $results[$participant->user->club->id]['score'] += $participant->score;
            } else {
                $results[$participant->user->club->id]['score'] = $participant->score;
            }

            $results[$participant->user->club->id]['club'] = $participant->user->club;

            if(isset($results[$participant->user->club->id]['competitions'][$participant->competition_id])) {
                $results[$participant->user->club->id]['competitions'][$participant->competition_id]['score'] += $participant->score;
            } else {
                $competition = $competitions->find($participant->competition_id)->toArray();
                $results[$participant->user->club->id]['competitions'][$participant->competition_id] = $competition;
                $results[$participant->user->club->id]['competitions'][$participant->competition_id]['score'] = $participant->score;
            }


            $member = $participant->toArray();
            unset($member['user']['club']);
            $results[$participant->user->club->id]['members'][] = $member;
        }

        // Makni keyeve s popisa natjecanja.
        foreach($results as $key => $result) {
            $results[$key]['competitions'] = array_values($results[$key]['competitions']);
        }

        // Sortiraj po ukupnim bodovima.
        usort($results, function($a, $b) {
            return $a['score'] - $b['score'];
        });

        return response()->json($results);
    }
}
