<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class TrelloController extends Controller
{
    protected $key;
    protected $secret;
    protected $token;

    public $boards;
    public $lists;
    public $cards;

    public function __construct()
    {
        $this->key = env('TRELLO_KEY');
        $this->secret = env('TRELLO_SECRET');
        $this->token = env('TRELLO_TOKEN');
    }
    
    public function index()
    {
        return Inertia::render('Trello');
    }

    // GET data from API
    public function boards()
    {
        $url = 'https://api.trello.com/1/members/me/boards';
        $query = [
            'key' => $this->key,
            'token' => $this->token,
        ];
        $response = Http::get($url, $query);

        $this->boards = $response;
        return $response;
    }

    public function lists($boardId)
    {
        $url = "https://api.trello.com/1/boards/$boardId/lists";
        $query = [
            'key' => $this->key,
            'token' => $this->token,
        ];
        $response = Http::get($url, $query);
        return $response;
    }

    public function cards($boardId)
    {
        $url = "https://api.trello.com/1/boards/$boardId/cards";
        $query = [
            'key' => $this->key,
            'token' => $this->token,
        ];
        $response = Http::get($url, $query);
        return $response;
    }

    public function card($cardId)
    {
        $url = "https://api.trello.com/1/cards/$cardId";
        $query = [
            'key' => $this->key,
            'token' => $this->token,
            'actions' => 'commentCard',
            'attachments' => true,
            'checklists' => 'all',
        ];
        $response = Http::get($url, $query);
        return $response;
    }

    // POST data to API
    public function storeComment($cardId)
    {
        $url = "https://api.trello.com/1/cards/$cardId/actions/comments";
        $email = $user = Auth::user()->email;
        $query = [
            'key' => $this->key,
            'token' => $this->token,
            // 'id' => $cardId,
            'text' => request()->text  . "\xA" . $email
        ];
        $response = Http::post($url, $query);
        return $response;
    }

    public function storeCard($listId)
    {
        $url = "https://api.trello.com/1/cards";
        $query = [
            'key' => $this->key,
            'token' => $this->token,
            'idList' => $listId,
            'name' => request()->name,
            'desc' => request()->desc
        ];
        $response = Http::post($url, $query);
        return $response;
    }
}
