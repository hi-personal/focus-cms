<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\UserSession;
use App\Models\UserMeta;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $userSessions = UserSession::where('user_id', $request->user()->id)->get()->all();
        $validationRules = config('validation_rules.user_metas.profile_metas');
        $metas = array_merge(
            array_fill_keys(array_keys($validationRules), null),
            UserMeta::getDefaults(),
            UserMeta::where('user_id', $request->user()->id)->whereIn('name', array_keys($validationRules))->get()->pluck('value', 'name')->toArray()
        );

        return view('profile.edit', [
            'user' => $request->user(),
            'userSessions' => $userSessions,
            'metas' => $metas,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Users tábla adatainak frissítése
        $request->user()->fill($request->only([
            'name',
            'nicename',
            'login',
            'display_name',
            'email'
        ]));

        // Ha az email változott, töröljük az email_verified_at mezőt
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at   = null;
        }

        // Mentés a users táblába
        $request->user()->save();


        // UserMetas tábla adatainak frissítése
        $validationRules = config('validation_rules.user_metas.profile_metas');
        $userProfileMetaKeys = array_keys($validationRules);
        $userProfileMetaParams = $request->only($userProfileMetaKeys);

        $validator = Validator::make($userProfileMetaParams, $validationRules);
        $validated = $validator->valid();

        foreach ($validated as $name => $value) {
            $request->user()->meta()->updateOrCreate(
                ['name'  => $name], // Feltétel: meta adat neve
                ['value' => $value] // Meta adat értéke és egyéb mezők
            );
        }

        UserMeta::updateOrCreate(
            ['user_id' => $request->user()->id, 'name' => 'auth_2fa_temp_secret'],
            [
                'value' => null,
                'valid' => null
            ]
        );


        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
