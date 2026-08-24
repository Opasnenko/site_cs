<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

const SERVER_HOST = '46.174.49.52';
const SERVER_PORT = 27013;
const CACHE_TTL = 110; // seconds; keeps anonymous API usage below 30 requests/hour
const GAMEQUERY_URL = 'https://api.gamequery.net/api/query/cs16/' . SERVER_HOST . '/' . SERVER_PORT;
// If you create a free GameQuery account, you may put the API key here.
// Leave empty to use the anonymous limit + local cache.
const GAMEQUERY_API_KEY = '';

function output(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function offline(string $reason = ''): never {
    output([
        'online' => false,
        'name' => 'BaseBuilder 14+',
        'map' => null,
        'players' => 0,
        'maxPlayers' => 0,
        'ip' => SERVER_HOST . ':' . SERVER_PORT,
        'source' => 'gamequery',
        'error' => $reason
    ]);
}

function fetchJson(string $url): array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
            CURLOPT_USERAGENT => 'BaseBuilder-Website/1.0'
        ]);
        if (GAMEQUERY_API_KEY !== '') {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/json',
                'X-API-Key: ' . GAMEQUERY_API_KEY
            ]);
        }
        $raw = curl_exec($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($raw === false || $err !== '' || $code < 200 || $code >= 300) {
            throw new RuntimeException('HTTP API error' . ($code ? ' ' . $code : ''));
        }
    } else {
        $headers = "Accept: application/json\r\nUser-Agent: BaseBuilder-Website/1.0\r\n";
        if (GAMEQUERY_API_KEY !== '') $headers .= 'X-API-Key: ' . GAMEQUERY_API_KEY . "\r\n";
        $ctx = stream_context_create(['http' => [
            'method' => 'GET', 'timeout' => 10, 'header' => $headers
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) throw new RuntimeException('HTTPS request failed');
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) throw new RuntimeException('Invalid JSON from GameQuery');
    return $data;
}

function cacheFile(): string {
    $dir = __DIR__ . '/cache';
    if (!is_dir($dir)) @mkdir($dir, 0755, true);
    return $dir . '/server_status.json';
}

function readCache(): ?array {
    $file = cacheFile();
    if (!is_file($file)) return null;
    if (time() - (int)@filemtime($file) > CACHE_TTL) return null;
    $raw = @file_get_contents($file);
    $data = $raw !== false ? json_decode($raw, true) : null;
    return is_array($data) ? $data : null;
}

function writeCache(array $data): void {
    @file_put_contents(cacheFile(), json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
}

$cached = readCache();
if ($cached !== null) output($cached);

try {
    $d = fetchJson(GAMEQUERY_URL);

    // GameQuery/GameDig normally returns players.online and players.max.
    $players = 0;
    $maxPlayers = 0;
    if (isset($d['players']) && is_array($d['players'])) {
        $players = (int)($d['players']['online'] ?? count($d['players']['list'] ?? []));
        $maxPlayers = (int)($d['players']['max'] ?? $d['maxplayers'] ?? 0);
    } else {
        $players = (int)($d['players'] ?? $d['numplayers'] ?? $d['playersCurrent'] ?? 0);
        $maxPlayers = (int)($d['maxplayers'] ?? $d['maxPlayers'] ?? $d['playersMax'] ?? 0);
    }

    $online = $d['online'] ?? $d['status'] ?? false;
    if (is_string($online)) $online = in_array(strtolower($online), ['online','alive','true','1'], true);

    $result = [
        'online' => (bool)$online,
        'name' => (string)($d['name'] ?? $d['hostname'] ?? 'BaseBuilder 14+'),
        'map' => $d['map'] ?? null,
        'players' => $players,
        'maxPlayers' => $maxPlayers,
        'ip' => SERVER_HOST . ':' . SERVER_PORT,
        'ping' => isset($d['ping']) ? (int)$d['ping'] : null,
        'source' => 'gamequery'
    ];

    writeCache($result);
    output($result);
} catch (Throwable $e) {
    // Return the previous cached result even if the external API temporarily fails.
    $file = cacheFile();
    if (is_file($file)) {
        $raw = @file_get_contents($file);
        $old = $raw !== false ? json_decode($raw, true) : null;
        if (is_array($old)) {
            $old['stale'] = true;
            output($old);
        }
    }
    offline($e->getMessage());
}
