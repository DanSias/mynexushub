<?php

namespace App\Http\Controllers;

use Auth;
use Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Team;
use App\Notifications\UserInvited;

class LoginController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        $currentUser = Socialite::driver($provider)->user();

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
        $team = $this->checkTeam($user);
       
        $user->touch();
        $user->markEmailAsVerified();

        Auth::login($user, true);

        return redirect()->intended('/');
    }

    public function inviteForm(Request $request)
    {
        return view('auth.invite');
    }

    public function inviteUser(Request $request)
    {
        if (! $this->checkEmail($request->email)) {
            abort(422, 'Invalid Email Address');
        }

        $email = $request->email;
        $name = $this->nameFromEmail($email);

        $check = ['email' => $email];
        $create = ['email' => $email, 'name' => $name];

        $user = User::updateOrCreate($check, $create);

        $user->notify(new UserInvited($name, $email));

        return back()->with('user', $user);
    }

    public function createProfile(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        // dd($user);
        return view('auth.invite-confirm', ['user' => $user]);
    }
    
    public function updateProfile(User $user)
    {
        $user->update(['password' => Hash::make(request()->password)]);
        $team = $this->checkTeam($user);
        $user->markEmailAsVerified();
        Auth::login($user, true);
        return $user;
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

    public function nameFromEmail($email)
    {
        $email = preg_replace('/[0-9]+/', '', $email);
        $emailArray = explode('@', $email);
        $name = $emailArray[0];
        $nameArray = explode('.', $name);
        
        return (isset($nameArray[1])) ? ucfirst($nameArray[0]) . ' ' . ucfirst($nameArray[1]) : ucFirst($nameArray[0]);
    }

    public function checkTeam($user)
    {
        $team = Team::where('user_id', $user->id)->first();

        if ($team == null) {
            $newTeam['user_id'] = $user->id;
            $newTeam['name'] = 'My Team';
            $newTeam['personal_team'] = 1;
            $makeTeam = Team::create($newTeam);

            $team = User::where('id', $user->id)->update(['current_team_id' => $makeTeam->id]);
        }

        return $team;
    }
}
