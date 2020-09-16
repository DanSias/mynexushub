<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedback = Feedback::with('user')
            ->with('user.role')
            ->orderBy('created_at', 'desc')
            ->get();
        
        foreach ($feedback as $f) {
            $c = new Carbon($f->created_at);
            $f->when = $c->diffForHumans();
            // $f->diff = new Carbon::diffForHumans($f->created_at);
        }

        return $feedback;
    }
}
