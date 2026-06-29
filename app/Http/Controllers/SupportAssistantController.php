<?php

namespace App\Http\Controllers;

use App\Services\SupportAssistantService;
use Illuminate\Http\Request;

class SupportAssistantController extends Controller
{
    protected $assistant;

    public function __construct(SupportAssistantService $assistant)
    {
        $this->assistant = $assistant;
    }

    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:500'
        ]);

        $response = $this->assistant->processQuery($request->message, auth()->user());

        return response()->json([
            'reply' => $response['message']
        ]);
    }
}
