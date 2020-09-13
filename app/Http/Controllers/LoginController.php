<?php

namespace App\Http\Controllers;

use Auth;
use Socialite;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Team;

class LoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $currentUser = ($provider == 'microsoft') 
            ? Socialite::with('azure')->user() 
            : Socialite::driver($provider)->user();

        $name = $this->formatName($currentUser->getName());
        $email = strtolower($currentUser->getEmail());

        // Check for authorized email
        if (! $this->checkEmail($email)) {
            return redirect('/login/' . $provider);
        }

        // Find user record, create if none found
        $user = User::where('email', $email)->first();
        if ($user) {
            $user->name = $name;
            $user->email = $email;
            $user->save();
        } else {
            $userDetails = [
                'name' => $name,
                'email' => $email,
            ];
            $userCheck = ['email' => $email];
            $user = User::updateOrCreate($userCheck, $userDetails);
        }

        // Check for team
        $team = Team::where('user_id', $user->id)->first();

        if ($team == null) {
            $newTeam['user_id'] = $user->id;
            $newTeam['name'] = 'My Team';
            $newTeam['personal_team'] = 1;
            $makeTeam = Team::create($newTeam);
            User::where('id', $user->id)->update(['current_team_id' => $makeTeam->id]);
        }

        $user->touch();
        Auth::login($user, true);

        return redirect()->intended();
    }

    // If name is Last, First - format as readable name
    private function formatName($name)
    {
        if (! strpos($name, ',') === false) {
            $nameArray = explode(', ', $name);
            $name = $nameArray[1] . ' ' . $nameArray[0];
        }
        return $name;
    }

    // Verify email address from approved domain or a guest
    private function checkEmail($email)
    {
        $domain = env('EMAIL_DOMAIN');
        $guests = explode(',', env('GUEST_USERS'));

        if (strpos($email, $domain) !== false) {
            return true;
        }
        if (in_array($email, $guests)) {
            return true;
        }
        return false;
    }
}
