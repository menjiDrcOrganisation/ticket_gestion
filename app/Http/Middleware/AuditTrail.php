<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditTrail
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        [$entityType, $entityId, $beforeValues] = $this->extractEntitySnapshot($request);

        try {
            $response = $next($request);

            $this->writeAudit(
                $request,
                $user->id,
                $entityType,
                $entityId,
                $beforeValues,
                $response->getStatusCode()
            );

            return $response;
        } catch (Throwable $exception) {
            $statusCode = $exception instanceof ValidationException
                ? 422
                : (method_exists($exception, 'getStatusCode') ? (int) $exception->getStatusCode() : 500);

            $this->writeAudit(
                $request,
                $user->id,
                $entityType,
                $entityId,
                $beforeValues,
                $statusCode,
                ['exception' => class_basename($exception)]
            );

            throw $exception;
        }
    }

    private function writeAudit(
        Request $request,
        int $userId,
        ?string $entityType,
        ?int $entityId,
        ?array $beforeValues,
        int $statusCode,
        array $extraAfterValues = []
    ): void {
        $afterValues = array_merge(
            $this->sanitizePayload($request->except(['_token', '_method'])),
            $extraAfterValues
        );

        AuditLog::create([
            'user_id' => $userId,
            'action' => $this->resolveAction($request),
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'url' => (string) $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status_code' => $statusCode,
            'before_values' => $beforeValues,
            'after_values' => $afterValues,
        ]);
    }

    private function resolveAction(Request $request): string
    {
        $action = match ($request->method()) {
            'POST' => 'create',
            'PUT', 'PATCH' => 'update',
            'DELETE' => 'delete',
            default => strtolower($request->method()),
        };

        $routeName = (string) $request->route()?->getName();

        if (Str::contains($routeName, 'changeStatus') || Str::contains($routeName, 'updateStatus')) {
            return 'status_change';
        }

        if (Str::contains($routeName, 'refund')) {
            return 'refund';
        }

        return $action;
    }

    private function extractEntitySnapshot(Request $request): array
    {
        $route = $request->route();

        if (! $route) {
            return [null, null, null];
        }

        foreach ($route->parameters() as $parameter) {
            if ($parameter instanceof Model) {
                return [
                    class_basename($parameter),
                    (int) $parameter->getKey(),
                    $this->sanitizePayload($parameter->getAttributes()),
                ];
            }

            if (is_numeric($parameter)) {
                return [null, (int) $parameter, null];
            }
        }

        return [null, null, null];
    }

    private function sanitizePayload(array $payload): array
    {
        $sanitized = [];

        foreach ($payload as $key => $value) {
            if ($this->isSensitiveKey((string) $key)) {
                $sanitized[$key] = '[FILTERED]';
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizePayload($value);
                continue;
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    private function isSensitiveKey(string $key): bool
    {
        $key = Str::lower($key);

        return Str::contains($key, [
            'password',
            'token',
            'secret',
            'key',
            'authorization',
            'cookie',
        ]);
    }
}
