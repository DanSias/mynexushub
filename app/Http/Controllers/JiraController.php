<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JiraController extends Controller
{
    protected $subdomain;
    protected $username;
    protected $token;

    public $projects;
    public $issues;
    // public $lists;

    public function __construct()
    {
        $this->subdomain = env('ATLASSIAN_SUBDOMAIN');
        $this->username = env('ATLASSIAN_USERNAME');
        $this->token = env('ATLASSIAN_TOKEN');
    }

    public function index()
    {
        return Inertia::render('Jira');
    }

    // GET data from API
    public function projects()
    {
        $url = "https://$this->subdomain.atlassian.net/rest/api/3/project/search";

        $response = Http::withBasicAuth($this->username, $this->token)
            ->get($url);
        
        $this->projects = $response;
        return $response;
    }

    public function columns($projectId)
    {
        $url = "https://$this->subdomain.atlassian.net/rest/agile/1.0/board/$projectId/configuration";

        $response = Http::withBasicAuth($this->username, $this->token)
            ->get($url);

        return $response;
    }

    public function issues($project = '')
    {
        $url = "https://$this->subdomain.atlassian.net/rest/api/3/search";

        if ($project == '') {
            $response = Http::withBasicAuth($this->username, $this->token)
                ->get($url);
        } else {
            $response = Http::withBasicAuth($this->username, $this->token)
                ->get($url);
        }
        
        $this->projects = $response;
        return $response;
    }

    public function issue($issueIdOrKey)
    {
        $url = "https://$this->subdomain.atlassian.net/rest/api/3/issue/$issueIdOrKey";

        $response = Http::withBasicAuth($this->username, $this->token)
            ->get($url);
        
        return $response;
    }

    // POST data to API
    public function storeIssue(Request $request)
    {
        $url = "https://$this->subdomain.atlassian.net/rest/api/3/issue";

        $summary = $request->summary;
        $fullDescription = $request->fullDescription;
        // $description = $request->description . "\n* bullets\n* for\n* Listing \nh1. Whatup";

        // $description = "
        //     h2. Look at me
        //     * listing
        //     * some
        //     * nouns
        // ";
        // $metricString = implode(', ', $request->metric);
        // $description .= "
        //     Metrics: $metricString
        // ";

        // $description .= $request->fullDescription;

        // Due Date
        if ($request->date) {
            $fields['duedate'] = $request->date;
        }
        // 

        
        // Description
        $issueContent[0] = [
            'type' => 'text',
            'text' => $fullDescription
        ];

        $descriptionContent[0] = [
            'type' => 'paragraph',
            'content' => $issueContent
        ];

        // Build Jira Object
        $fields['project']['key'] = 'NEXUS';
        $fields['issuetype']['name'] = 'Task';
        $fields['summary'] = $summary;
        // $fields['description'] = $description;
        $fields['description'] = [
            'type' => 'doc',
            'version' => 1,
            'content' => $descriptionContent
        ];


        $query = [
            'fields' => $fields
        ];

        $response = Http::withBasicAuth($this->username, $this->token)
            ->post($url, $query);

        return $response;
    }

    public function storeComment($issueId)
    {
        $url = "https://$this->subdomain.atlassian.net/rest/api/3/issue/$issueId/comment";

        $commentContent[0] = [
            'type' => 'text',
            'text' => request()->text
        ];

        $commentContent[1] = [
            'type' => 'text',
            'text' => ' ~' . auth()->user()->name
        ];

        $descriptionContent[0] = [
            'type' => 'paragraph',
            'content' => $commentContent
        ];

        $fields['body'] = [
            'type' => 'doc',
            'version' => 1,
            'content' => $descriptionContent
        ];

        $response = Http::withBasicAuth($this->username, $this->token)
            ->post($url, $fields);

        return $response;
    }

    public function buildDescription($request)
    {
        $props = [
            'delivery',
            'metric'
        ];
    }

}
