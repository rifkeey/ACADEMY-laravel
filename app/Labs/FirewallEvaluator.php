<?php

namespace App\Labs;

/** Ordered firewall rule evaluation for the network labs: first match wins, default deny. */
final class FirewallEvaluator
{
    /**
     * @param  list<array{action: string, source: string, destination: string, protocol: string, port: int|string}>  $rules
     * @param  array{source: string, destination: string, protocol: string, port: int}  $flow
     * @return array{action: string, ruleIndex: int|null}
     */
    public static function evaluate(array $rules, array $flow): array
    {
        foreach ($rules as $i => $r) {
            if ($r['source'] !== 'any' && $r['source'] !== $flow['source']) {
                continue;
            }
            if ($r['destination'] !== 'any' && $r['destination'] !== $flow['destination']) {
                continue;
            }
            if ($r['protocol'] !== 'any' && $r['protocol'] !== $flow['protocol']) {
                continue;
            }
            if ($r['port'] !== 'any' && $r['port'] !== $flow['port']) {
                continue;
            }

            return ['action' => $r['action'], 'ruleIndex' => $i];
        }

        return ['action' => 'deny', 'ruleIndex' => null];
    }

    /** Keeps only well-formed rules that reference known zones, up to $maxRules. */
    public static function sanitize(mixed $input, array $zones, int $maxRules): array
    {
        if (! is_array($input) || ! array_is_list($input)) {
            return [];
        }
        $zoneSet = [...$zones, 'any'];
        $out = [];
        foreach (array_slice($input, 0, $maxRules) as $r) {
            if (! is_array($r)) {
                continue;
            }
            $action = in_array($r['action'] ?? null, ['allow', 'deny'], true) ? $r['action'] : null;
            $protocol = in_array($r['protocol'] ?? null, ['tcp', 'udp', 'any'], true) ? $r['protocol'] : null;
            $source = is_string($r['source'] ?? null) && in_array($r['source'], $zoneSet, true) ? $r['source'] : null;
            $destination = is_string($r['destination'] ?? null) && in_array($r['destination'], $zoneSet, true) ? $r['destination'] : null;
            $rawPort = $r['port'] ?? null;
            $port = null;
            if ($rawPort === 'any') {
                $port = 'any';
            } elseif ((is_int($rawPort) || (is_string($rawPort) && ctype_digit($rawPort))) && (int) $rawPort > 0 && (int) $rawPort < 65536) {
                $port = (int) $rawPort;
            }
            if ($action && $protocol && $source && $destination && $port !== null) {
                $out[] = compact('action', 'source', 'destination', 'protocol', 'port');
            }
        }

        return $out;
    }
}
