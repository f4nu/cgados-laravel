<?php

namespace App\Http\Controllers;

use App\Http\Resources\TerminalCommandResource;
use App\Models\SessionTerminalCommand;
use App\Models\TerminalCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

class TerminalCommandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $command): TerminalCommandResource {
        $terminalCommand = TerminalCommand::where('command', $command)->where('enabled', 1)->firstOrFail();
        $body = json_decode($request->getContent());
        $terminalSession = ($body->terminal_session ?? '') ?: uniqid();

        $request->session()->put('terminal_session', $terminalSession);
        (new SessionTerminalCommand())->create([
            'terminal_session' => $terminalSession,
            'terminal_command_id' => $terminalCommand->id,
            'args' => json_encode(request()->all())
        ]);
        return new TerminalCommandResource($terminalCommand);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TerminalCommand $terminalCommand)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TerminalCommand $terminalCommand)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TerminalCommand $terminalCommand)
    {
        //
    }
}
