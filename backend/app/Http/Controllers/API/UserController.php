<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class UserController extends Controller
{
    // Pronađi sve korisnike s klubovima, sortiraj ih po imenu.
    public function index()
    {
        $users = User::with('club')->orderBy('created_at', 'DESC')->get();
    
        return response()->json($users);
    }

    // Pronađi korisnika po ID-u.
    public function show($id)
    {
        $user = User::with('club', 'participants', 'participants.competition')->find($id);
        if(!$user) {
            return response()->json(['message' => 'Korisnik nije pronađen.'], 404);
        }

        return response()->json($user);
    }

    // Unesi novog korisnika.
    public function store(Request $request)
    {
        $input = $request->all();

        // Provjeri ulazne podatke, ime i spol su obavezni.
        $validator = Validator::make($input, [
            'logo' => 'nullable|image',
            'name' => 'required',
            'gender' => 'required'
        ]);

        if($validator->fails()){
            return response()->json(['message' => 'Neispravni podaci.'], 400);
        }

        // Spremi logo, ako postoji.
        if($request->file('logo')) {
            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $user = User::create($input);

        return response()->json(['status' => 1, 'id' => $user->id]);
    }

    // Uredi korisnika.
    public function update(Request $request, User $user)
    {
        $input = $request->all();

        // Ako postoji novi logo, izbriši stari logo i spremi novi.
        if($request->file('logo')) {
            if($user->logo) {
                Storage::delete('logo/' . $user->logo);
            }

            $input['logo'] = $this->storeLogo($request->file('logo'));
        }

        $user->update($input);

        return response()->json(['status' => 1]);
    }

    // Izbriši korisnika.
    public function destroy(User $user)
    {
        // Izbriši logo korisnika.
        Storage::delete('logo/' . $user->logo);
        
        $user->delete();

        return response()->json(['status' => 1]);
    }

    // Spremi logo korisnika s nasumičnim imenom.
    private function storeLogo($file)
    {
        $filename = 'user_' . uniqid() . md5($file->getClientOriginalName()) . '.' . $file->getClientOriginalExtension();
        $file->storePubliclyAs('logo', $filename);

        return $filename;
    }
}
