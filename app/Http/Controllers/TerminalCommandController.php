<?php

namespace App\Http\Controllers;

use App\Http\Resources\TerminalCommandResource;
use App\Models\TerminalCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function show(string $command): TerminalCommandResource {
        $terminalCommand = TerminalCommand::where('command', $command)->firstOrFail();
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
