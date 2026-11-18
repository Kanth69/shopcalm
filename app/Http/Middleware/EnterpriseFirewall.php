<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnterpriseFirewall
{
    /**
     * Known malicious scanner / attack tool signatures in User-Agent headers.
     */
    protected static array $badUserAgents = [
        'sqlmap',
        'nikto',
        'dirbuster',
        'gobuster',
        'wpscan',
        'acunetix',
        'havij',
        'masscan',
        'nmap',
        'zgrab',
        'nessus',
        'openvas',
        'metasploit',
        'hydra',
        'burpsuite',
    ];

    /**
     * Dangerous query string / payload patterns (SQLi, RCE, Path Traversal, Null Byte).
     */
    protected static array $maliciousPatterns = [
        '/((\%2e|\.){2,}(\%2f|[\/\\\]))/i',                     // Path Traversal: ../ or ..\
        '/(\/etc\/(passwd|shadow|hosts))/i',                     // Linux system files
        '/(win\.ini|boot\.ini)/i',                               // Windows system files
        '/(\%00|\x00)/',                                         // Null Byte Injection
        '/(union(\s+all)?\s+select)/i',                          // SQLi: UNION SELECT
        '/(benchmark\s*\(\s*\d+\s*\,|sleep\s*\(\s*\d+\s*\))/i',  // SQLi: Blind Time-based (sleep, benchmark)
        '/(waitfor\s+delay\s+[\'"])/i',                          // SQLi: MSSQL waitfor delay
        '/(information_schema|pg_catalog|sys\.tables)/i',        // SQLi: Schema probing
        '/(eval\s*\(|base64_decode\s*\(|system\s*\(|passthru\s*\()/i', // RCE / Shell execution
        '/(php:\/\/input|php:\/\/filter|data:\/\/text)/i',       // PHP Stream Wrapper Wrappers
        '/(<script[^>]*>.*<\/script>)/is',                       // Inline script injection in query params
    ];

    /**
     * Handle incoming request through the Enterprise Firewall.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Skip static assets (.css, .js, images, fonts) for ultra-fast performance
        $path = $request->path();
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|webp|ico|woff|woff2|ttf|eot)$/i', $path)) {
            return $next($request);
        }

        $ip = $request->ip() ?? '0.0.0.0';
        $userAgent = strtolower($request->userAgent() ?? '');

        // 2. Shield: Block Known Malicious Scanners & Exploitation Crawlers
        foreach (self::$badUserAgents as $badAgent) {
            if (str_contains($userAgent, $badAgent)) {
                $this->logThreat($ip, "Malicious Scanner Blocked: {$badAgent}", $request);
                return response('Access Denied: Enterprise Security Policy.', 403);
            }
        }

        // 3. Shield: Inspect Query String & Raw Input for Exploits
        $queryString = rawurldecode($request->getQueryString() ?? '');
        $rawUrl = $request->fullUrl();

        foreach (self::$maliciousPatterns as $pattern) {
            if (preg_match($pattern, $queryString) || preg_match($pattern, $rawUrl)) {
                $this->logThreat($ip, "Exploit Attempt Blocked (Pattern: {$pattern})", $request);
                return response('Forbidden Request: Malicious Pattern Detected.', 403);
            }
        }

        // 4. Shield: Rate-Based Layer 7 Micro-Flood Defense (Max 180 requests per 10s per IP)
        if ($this->isRateSpiking($ip)) {
            $this->logThreat($ip, "L7 Flood Spike Throttled", $request);
            return response('Too Many Requests. Slow down.', 429, [
                'Retry-After' => '10',
            ]);
        }

        return $next($request);
    }

    /**
     * Check if an IP is rapidly spiking requests beyond normal human interaction.
     */
    protected function isRateSpiking(string $ip): bool
    {
        // Whitelist localhost/loopback
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return false;
        }

        $cacheKey = 'waf_spike_' . md5($ip);
        $count = (int) Cache::get($cacheKey, 0);

        if ($count >= 180) {
            return true;
        }

        Cache::put($cacheKey, $count + 1, now()->addSeconds(10));
        return false;
    }

    /**
     * Record threat log for security monitoring.
     */
    protected function logThreat(string $ip, string $reason, Request $request): void
    {
        Log::warning("[WAF SHIELD BLOCKED] {$reason}", [
            'ip'         => $ip,
            'url'        => $request->fullUrl(),
            'method'     => $request->method(),
            'user_agent' => $request->userAgent(),
            'timestamp'  => now()->toIso8601String(),
        ]);
    }
}
