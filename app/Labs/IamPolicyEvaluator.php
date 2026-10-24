<?php

namespace App\Labs;

/**
 * A teaching model of identity-based IAM policy evaluation, shaped like AWS IAM JSON policies.
 *
 *   1. An explicit Deny that matches the request wins.
 *   2. Otherwise a matching Allow grants access.
 *   3. Otherwise access is implicitly denied.
 *
 * Actions match case-insensitively and resources case-sensitively; both support the * and ?
 * wildcards. Deliberately out of scope (and rejected, so learners are not misled): Condition,
 * NotAction, NotResource, Principal, resource-based policies, permission boundaries and SCPs.
 */
final class IamPolicyEvaluator
{
    private const MAX_STATEMENTS = 20;

    private const MAX_ITEMS = 50;

    /**
     * Parses and validates a learner's policy document.
     *
     * @return array{ok: true, statements: list<array{effect: string, actions: list<string>, resources: list<string>, sid: string|null}>}|array{ok: false, error: string}
     */
    public static function parse(mixed $json): array
    {
        if (! is_string($json) || trim($json) === '' || strlen($json) > 20_000) {
            return ['ok' => false, 'error' => __('Paste a JSON policy document (at most 20,000 characters).')];
        }
        $doc = json_decode($json, true);
        if (! is_array($doc)) {
            return ['ok' => false, 'error' => __('The policy is not valid JSON: :error.', ['error' => json_last_error_msg()])];
        }
        $raw = $doc['Statement'] ?? null;
        if (is_array($raw) && ! array_is_list($raw)) {
            $raw = [$raw];      // a single statement object is allowed, as in AWS
        }
        if (! is_array($raw) || ! $raw) {
            return ['ok' => false, 'error' => __('The policy needs a "Statement" list.')];
        }
        if (count($raw) > self::MAX_STATEMENTS) {
            return ['ok' => false, 'error' => __('Use at most :max statements.', ['max' => self::MAX_STATEMENTS])];
        }

        $statements = [];
        foreach ($raw as $i => $s) {
            $n = is_array($s) && is_string($s['Sid'] ?? null) ? "\"{$s['Sid']}\"" : __('statement :n', ['n' => $i + 1]);
            if (! is_array($s)) {
                return ['ok' => false, 'error' => __(':statement must be an object.', ['statement' => $n])];
            }
            foreach (['Condition', 'NotAction', 'NotResource', 'Principal', 'NotPrincipal'] as $unsupported) {
                if (array_key_exists($unsupported, $s)) {
                    return ['ok' => false, 'error' => __(':statement uses ":element", which this simulator does not evaluate. Express the requirement with Effect, Action and Resource.', ['statement' => $n, 'element' => $unsupported])];
                }
            }
            if (! in_array($s['Effect'] ?? null, ['Allow', 'Deny'], true)) {
                return ['ok' => false, 'error' => __(':statement needs "Effect": "Allow" or "Deny".', ['statement' => $n])];
            }
            $actions = self::list($s['Action'] ?? null);
            $resources = self::list($s['Resource'] ?? null);
            if (! $actions || ! $resources) {
                return ['ok' => false, 'error' => __(':statement needs "Action" and "Resource" (a string or a list of strings).', ['statement' => $n])];
            }
            $statements[] = ['effect' => $s['Effect'], 'actions' => $actions, 'resources' => $resources, 'sid' => is_string($s['Sid'] ?? null) ? $s['Sid'] : null];
        }

        return ['ok' => true, 'statements' => $statements];
    }

    /** @return array{decision: 'allow'|'deny', reason: 'explicit-deny'|'allowed'|'implicit-deny'} */
    public static function evaluate(array $statements, string $action, string $resource): array
    {
        $allowed = false;
        foreach ($statements as $s) {
            if (! self::matchesAny($s['actions'], $action, true) || ! self::matchesAny($s['resources'], $resource, false)) {
                continue;
            }
            if ($s['effect'] === 'Deny') {
                return ['decision' => 'deny', 'reason' => 'explicit-deny'];
            }
            $allowed = true;
        }

        return $allowed ? ['decision' => 'allow', 'reason' => 'allowed'] : ['decision' => 'deny', 'reason' => 'implicit-deny'];
    }

    /** @return list<string> */
    private static function list(mixed $v): array
    {
        $items = is_string($v) ? [$v] : (is_array($v) && array_is_list($v) ? $v : []);
        $items = array_values(array_filter($items, fn ($x) => is_string($x) && $x !== '' && strlen($x) <= 300));

        return array_slice($items, 0, self::MAX_ITEMS);
    }

    private static function matchesAny(array $patterns, string $value, bool $caseInsensitive): bool
    {
        foreach ($patterns as $p) {
            $regex = '/^'.str_replace(['\*', '\?'], ['.*', '.'], preg_quote($p, '/')).'$/'.($caseInsensitive ? 'i' : '');
            if (preg_match($regex, $value) === 1) {
                return true;
            }
        }

        return false;
    }
}
