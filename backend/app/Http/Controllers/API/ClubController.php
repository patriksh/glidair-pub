<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Club;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class ClubController extends Controller
{
    // Pronađi sve klubove, sortiraj ih po imenu.
    public function index()
    {
        $clubs = Club::orderBy('created_at', 'DESC')->get();
    
        return response()->json($clubs);
    }

    // Pronađi klub po ID-u.
    public function show($id)
    {
        $club = Club::with('users')->find($id);
        if(!$club) {
            return response()->json(['message' => 'Klub nije pronađen.'], 404);
        }

        return response()->json($club);
    }

    // Unesi novi klub.
    public function store(Request $request)
    {
        $input = $request->all();

        // Provjeri ulazne podatke, ime je obavezno.
        $validator = Validator::make($input, [
            'logo' => 'nullable|image',
            'name' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['message' => 'Neispravni podaci.'], 400);
        }

        // Spremi logo, ako postoji.
        if($request->file('logo')) {
            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $club = Club::create($input);

        return response()->json(['status' => 1, 'id' => $club->id]);
    }

    // Uredi klub.
    public function update(Request $request, Club $club)
    {
        $input = $request->all();

        // Ako postoji novi logo, izbriši stari logo i spremi novi.
        if($request->file('logo')) {
            if($club->logo) {
                Storage::delete('logo/' . $club->logo);
            }

            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $club->update($input);

        return response()->json(['status' => 1]);
    }

    // Izbriši klub.
    public function destroy(Club $club)
    {
        // Izbriši logo natjecanja.
        Storage::delete('logo/' . $club->logo);

        $club->delete();

        return response()->json(['status' => 1]);
    }

    // Spremi logo kluba s nasumičnim imenom.
    private function storeLogo($file)
    {
        $filename = 'club_' . uniqid() . md5($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        $file->storePubliclyAs('logo', $filename);

        return $filename;
    }
}
