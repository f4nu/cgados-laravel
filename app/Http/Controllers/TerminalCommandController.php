<?php

namespace App\Http\Controllers;

use App\Http\Resources\TerminalCommandResource;
use App\Models\SessionTerminalCommand;
use App\Models\TerminalCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class TerminalCommandController extends Controller
{
    public function show(Request $request, string $command): TerminalCommandResource {
        if ($command === 'intro')
            $command = 'intro-2';
        
        if ($command === 'intro-2') {
            if (TerminalCommand::getNowPercentPrecise() >= 100)
                $command = 'login';
        }
        
        $terminalCommand = TerminalCommand::where('command', $command)->where('enabled', 1)->firstOrFail();
        $body = json_decode($request->getContent());
        $terminalSession = ($body->terminal_session ?? '') ?: uniqid();

        $args = $body->args ?? [];
        $request->session()->put('terminal_session', $terminalSession);
        (new SessionTerminalCommand())->create([
            'terminal_session' => $terminalSession,
            'terminal_command_id' => $terminalCommand->id,
            'args' => json_encode($args),
        ]);
        $terminalCommand->args = $args;
        return new TerminalCommandResource($terminalCommand);
    }

    public function space(Request $request): JsonResponse {
        Log::debug('User breached the space.');
        return new JsonResponse([
            'message' => 'See you, space cowboy. 🚀',
        ]);
    }
}
