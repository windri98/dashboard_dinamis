<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\ApiEndpoint;
use App\Models\ApiAccessLog;
use App\Models\ApiRateLimit;
use Symfony\Component\HttpFoundation\Response;

class ApiGuardMiddleware
{
    public function handle(Request $request, Closure $next, $apiEndpointId = null): Response
    {
        $startTime = microtime(true);
        $clientIP = $this->getClientIP($request);
        
        // Jika tidak ada ID endpoint, cari berdasarkan route
        if (!$apiEndpointId) {
            $apiEndpoint = ApiEndpoint::byEndpoint(
                $request->path(),
                $request->method()
            )->first();
            
            if (!$apiEndpoint) {
                return $this->denyAccess(
                    'API endpoint tidak terdaftar',
                    null,
                    $clientIP,
                    $request,
                    $startTime,
                    404
                );
            }
            
            $apiEndpointId = $apiEndpoint->id;
        } else {
            $apiEndpoint = ApiEndpoint::find($apiEndpointId);
        }
        
        if (!$apiEndpoint) {
            return $this->denyAccess(
                'API endpoint tidak ditemukan',
                $apiEndpointId,
                $clientIP,
                $request,
                $startTime,
                404
            );
        }
        
        // 1. Cek apakah API aktif
        if (!$apiEndpoint->is_active) {
            return $this->denyAccess(
                'API tidak aktif',
                $apiEndpointId,
                $clientIP,
                $request,
                $startTime
            );
        }
        
        // 2. Cek IP Blacklist
        if ($this->isIPBlacklisted($apiEndpoint, $clientIP)) {
            return $this->denyAccess(
                'IP address dalam blacklist',
                $apiEndpointId,
                $clientIP,
                $request,
                $startTime
            );
        }
        
        // 3. Cek IP Whitelist
        if ($apiEndpoint->use_ip_restriction) {
            if (!$this->isIPWhitelisted($apiEndpoint, $clientIP)) {
                return $this->denyAccess(
                    'IP address tidak diizinkan',
                    $apiEndpointId,
                    $clientIP,
                    $request,
                    $startTime
                );
            }
        }
        
        // 4. Cek Rate Limiting
        if ($apiEndpoint->use_rate_limit) {
            $rateLimitCheck = $this->checkRateLimit($apiEndpoint, $clientIP);
            
            if (!$rateLimitCheck['allowed']) {
                return $this->denyAccess(
                    'Rate limit exceeded. Coba lagi dalam ' . $rateLimitCheck['retry_after'] . ' detik',
                    $apiEndpointId,
                    $clientIP,
                    $request,
                    $startTime,
                    429
                );
            }
        }
        
        // 5. Log akses yang berhasil
        $this->logAccess(
            $apiEndpointId,
            $clientIP,
            $request,
            true,
            null,
            $startTime
        );
        
        // Lanjutkan request
        $response = $next($request);
        
        return $response;
    }
    
    private function isIPBlacklisted(ApiEndpoint $api, string $clientIP): bool
    {
        if (empty($api->ip_blacklist)) {
            return false;
        }
        
        return $this->ipInList($clientIP, $api->ip_blacklist);
    }
    
    private function isIPWhitelisted(ApiEndpoint $api, string $clientIP): bool
    {
        if (empty($api->ip_whitelist)) {
            return false;
        }
        
        return $this->ipInList($clientIP, $api->ip_whitelist);
    }
    
    private function ipInList(string $ip, array $list): bool
    {
        foreach ($list as $range) {
            $range = trim($range);
            
            // CIDR notation
            if (strpos($range, '/') !== false) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            } else {
                if ($ip === $range) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $mask) = explode('/', $range);
        $ip_long = ip2long($ip);
        $subnet_long = ip2long($subnet);
        $mask_long = -1 << (32 - (int)$mask);
        
        return ($ip_long & $mask_long) === ($subnet_long & $mask_long);
    }
    
    private function checkRateLimit(ApiEndpoint $api, string $clientIP): array
    {
        $now = now();
        
        // Cari atau buat rate limit record
        $rateLimit = ApiRateLimit::firstOrCreate(
            [
                'api_endpoint_id' => $api->id,
                'ip_address' => $clientIP,
            ],
            [
                'request_count' => 0,
                'window_start' => $now,
            ]
        );
        
        $windowAge = $now->diffInSeconds($rateLimit->window_start);
        
        // Reset window jika sudah lewat periode
        if ($windowAge >= $api->rate_limit_period) {
            $rateLimit->update([
                'request_count' => 1,
                'window_start' => $now,
            ]);
            
            return ['allowed' => true];
        }
        
        // Cek apakah sudah melebihi limit
        if ($rateLimit->request_count >= $api->rate_limit_max) {
            $retryAfter = $api->rate_limit_period - $windowAge;
            
            return [
                'allowed' => false,
                'retry_after' => $retryAfter,
            ];
        }
        
        // Increment counter
        $rateLimit->increment('request_count');
        
        return ['allowed' => true];
    }
    
    private function getClientIP(Request $request): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
        ];
        
        foreach ($ipKeys as $key) {
            if ($request->server($key)) {
                $ip = $request->server($key);
                
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        
        return $request->ip();
    }
    
    private function logAccess(
        int $apiEndpointId,
        string $clientIP,
        Request $request,
        bool $granted,
        ?string $blockReason,
        float $startTime,
        int $httpStatus = 200
    ): void {
        $executionTime = microtime(true) - $startTime;
        
        ApiAccessLog::create([
            'api_endpoint_id' => $apiEndpointId,
            'ip_address' => $clientIP,
            'user_agent' => $request->userAgent(),
            'request_method' => $request->method(),
            'request_uri' => $request->fullUrl(),
            'request_payload' => $request->all(),
            'response_status' => $httpStatus,
            'access_granted' => $granted,
            'block_reason' => $blockReason,
            'execution_time' => $executionTime,
        ]);
    }
    
    private function denyAccess(
        string $reason,
        ?int $apiEndpointId,
        string $clientIP,
        Request $request,
        float $startTime,
        int $httpStatus = 403
    ): Response {
        if ($apiEndpointId) {
            $this->logAccess(
                $apiEndpointId,
                $clientIP,
                $request,
                false,
                $reason,
                $startTime,
                $httpStatus
            );
        }
        
        return response()->json([
            'error' => $reason,
        ], $httpStatus);
    }
}