<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogger
{
    public static function request(Request $request, string $action = null): void
    {
        if (!$request->user() || $request->is('audit*')) return;

        $route = $request->route();
        $routeName = $route ? $route->getName() : null;

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => $action ?: strtolower($request->method()) . ' ' . ($routeName ?: $request->path()),
            'route' => $routeName,
            'method' => $request->method(),
            'ip_address' => $request->ip(),
            'metadata' => ['path' => $request->path()],
        ]);
    }
}