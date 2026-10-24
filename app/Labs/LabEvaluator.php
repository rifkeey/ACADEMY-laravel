<?php

namespace App\Labs;

use App\Labs\Kql\Kql;
use App\Labs\Kql\KqlError;

/**
 * Server-side lab marking. Pure and deterministic: given a lab's public content, its private
 * validator and the learner's answers, returns per-task results and whether the lab is passed.
 *
 * Task types: text, number, choice, multi, kql, firewall, grid, iam-policy, deliverable.
 */
final class LabEvaluator
{
    /**
     * @return array{results: list<array>, score: int, maxScore: int, passed: bool}
     */
    public static function evaluate(array $content, array $validator, array $answers, int $passPercent, string $now): array
    {
        $results = [];
        foreach ($content['tasks'] as $task) {
            $v = $validator['tasks'][$task['id']] ?? null;
            $answer = $answers[$task['id']] ?? null;
            $base = ['taskId' => $task['id'], 'points' => (int) $task['points']];

            if (! $v) {
                $results[] = $base + ['correct' => false, 'pointsAwarded' => 0, 'feedback' => __('This task has no validator configured.'), 'detail' => null];

                continue;
            }

            [$correct, $detail] = self::check($v, $task, $answer, $content, $now);

            $results[] = $base + [
                'correct' => $correct,
                'pointsAwarded' => $correct ? (int) $task['points'] : 0,
                'feedback' => $correct ? $v['feedback']['correct'] : $v['feedback']['incorrect'],
                'detail' => $detail,
            ];
        }

        $score = array_sum(array_column($results, 'pointsAwarded'));
        $max = array_sum(array_column($results, 'points'));

        // Integer arithmetic: passed ⇔ score × 100 ≥ passPercent × maxScore.
        return ['results' => $results, 'score' => $score, 'maxScore' => $max, 'passed' => $max > 0 && $score * 100 >= $passPercent * $max];
    }

    /** @return array{0: bool, 1: string|null} */
    private static function check(array $v, array $task, mixed $answer, array $content, string $now): array
    {
        switch ($v['type']) {
            case 'text':
                if (! is_string($answer) || mb_strlen($answer) > 2000) {
                    return [false, null];
                }
                $a = self::normaliseText($answer);
                foreach ($v['accept'] as $acc) {
                    if (self::normaliseText($acc) === $a) {
                        return [true, null];
                    }
                }
                if (! empty($v['pattern'])) {
                    $re = '/'.str_replace('/', '\/', $v['pattern']).'/iu';

                    return [@preg_match($re, trim($answer)) === 1, null];
                }

                return [false, null];

            case 'number':
                $n = is_int($answer) || is_float($answer) ? $answer : (is_string($answer) ? str_replace(',', '', trim($answer)) : null);
                $ok = is_numeric($n) && abs($n - $v['value']) <= ($v['tolerance'] ?? 0);

                return [$ok, null];

            case 'choice':
                return [is_string($answer) && $answer === $v['correct'], null];

            case 'multi':
                $set = is_array($answer) ? array_values(array_unique(array_filter($answer, 'is_string'))) : [];
                $ok = count($set) === count($v['correct']) && ! array_diff($v['correct'], $set);

                return [$ok, null];

            case 'kql':
                if (! is_string($answer) || trim($answer) === '') {
                    return [false, __('No query submitted.')];
                }
                if (($content['workspace']['kind'] ?? null) !== 'kql') {
                    return [false, null];
                }
                try {
                    $res = Kql::run($answer, $content['workspace']['tables'], $now);
                } catch (KqlError $e) {
                    return [false, __('Query error: :error', ['error' => $e->getMessage()])];
                } catch (\Throwable) {
                    return [false, __('Query could not be executed.')];
                }
                if (! in_array($v['column'], $res['columns'], true)) {
                    return [false, __('Your result does not contain a \':column\' column.', ['column' => $v['column']])];
                }
                $count = count($res['rows']);
                if (isset($v['maxRows']) && $count > $v['maxRows']) {
                    return [false, __('Your query returned :count rows; narrow it to at most :max.', ['count' => $count, 'max' => $v['maxRows']])];
                }
                $got = array_map(fn ($r) => self::cellKey($r[$v['column']] ?? null), $res['rows']);
                $want = array_map(fn ($c) => self::cellKey($c), $v['values']);
                // Optional "unique": each value must appear once. Used where an unaggregated query
                // (such as the console's starter query) would otherwise pass by chance.
                if (($v['unique'] ?? false) && count($got) !== count(array_unique($got))) {
                    return [false, __('Your query returned :count rows with repeated :column values; aggregate them (for example with summarize or distinct) so each value appears once.', ['count' => $count, 'column' => $v['column']])];
                }
                $ok = ($v['mode'] ?? 'set') === 'ordered'
                    ? $got === $want
                    : count(array_unique($got)) === count(array_unique($want)) && ! array_diff($want, $got) && ! array_diff($got, $want);

                return [$ok, $ok ? null : __('Your query returned :count row(s), but not the expected result.', ['count' => $count])];

            case 'firewall':
                if (($task['input']['type'] ?? null) !== 'firewall') {
                    return [false, null];
                }
                $rules = FirewallEvaluator::sanitize($answer, $task['input']['zones'], (int) $task['input']['maxRules']);
                if (! $rules) {
                    return [false, __('No valid rules submitted.')];
                }
                $failures = array_values(array_filter($v['flows'], fn ($f) => FirewallEvaluator::evaluate($rules, $f)['action'] !== $f['expect']));
                $total = count($v['flows']);
                if (! $failures) {
                    return [true, __('All :total test flows behaved as required.', ['total' => $total])];
                }
                $first = $failures[0];

                return [false, __($first['expect'] === 'allow' ? ':failed of :total test flows failed, e.g. ":flow" should be allowed.' : ':failed of :total test flows failed, e.g. ":flow" should be blocked.', ['failed' => count($failures), 'total' => $total, 'flow' => $first['label']])];

            case 'iam-policy':
                $parsed = IamPolicyEvaluator::parse($answer);
                if (! $parsed['ok']) {
                    return [false, $parsed['error']];
                }
                $failures = array_values(array_filter($v['requests'], fn ($r) => IamPolicyEvaluator::evaluate($parsed['statements'], $r['action'], $r['resource'])['decision'] !== $r['expect']));
                $total = count($v['requests']);
                if (! $failures) {
                    return [true, __('All :total test requests were decided as required.', ['total' => $total])];
                }
                $first = $failures[0];

                return [false, __($first['expect'] === 'allow' ? ':failed of :total test requests were decided incorrectly, e.g. ":request" should be allowed.' : ':failed of :total test requests were decided incorrectly, e.g. ":request" should be denied.', ['failed' => count($failures), 'total' => $total, 'request' => $first['label']])];

            case 'deliverable':
                // Completeness check only: enough words and the required points mentioned. The
                // quality of the reasoning is judged by the learner against the rubric.
                if (! is_string($answer) || mb_strlen($answer) > 20_000) {
                    return [false, __('Write your deliverable in the box (at most 20,000 characters).')];
                }
                $words = preg_match_all('/[\p{L}\p{N}][\p{L}\p{N}\'._\/-]*/u', $answer);
                $min = (int) ($v['minWords'] ?? 0);
                if ($words < $min) {
                    return [false, __('Your deliverable has :words words; write at least :min.', ['words' => $words, 'min' => $min])];
                }
                $text = mb_strtolower($answer);
                $missing = [];
                foreach ($v['mustMention'] ?? [] as $group) {
                    $terms = (array) ($group['any'] ?? []);
                    if (! array_filter($terms, fn ($t) => str_contains($text, mb_strtolower($t)))) {
                        $missing[] = $group['label'];
                    }
                }
                if ($missing) {
                    return [false, __('Your deliverable does not yet cover: :points.', ['points' => implode('; ', $missing)])];
                }

                return [true, __(':words words; all required points are covered. Now check it against the rubric.', ['words' => $words])];

            case 'grid':
                $grid = is_array($answer) && ! array_is_list($answer) ? $answer : [];
                $total = 0;
                $right = 0;
                foreach ($v['cells'] as $rowId => $cols) {
                    foreach ($cols as $colId => $allowed) {
                        $total++;
                        $given = $grid[$rowId][$colId] ?? null;
                        if (is_string($given) && in_array($given, $allowed, true)) {
                            $right++;
                        }
                    }
                }

                return [$total > 0 && $right / $total >= $v['minCorrectRatio'], "{$right} of {$total} entries match the expected assessment."];
        }

        return [false, null];
    }

    /** Trims, lower-cases, collapses spaces and strips surrounding quotes or a trailing full stop. */
    public static function normaliseText(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', mb_strtolower(trim($s)));

        return preg_replace('/^["\'`]+|["\'`.]+$/u', '', $s);
    }

    private static function cellKey(mixed $v): string
    {
        if ($v === null) {
            return '';
        }
        if (is_bool($v)) {
            return $v ? 'true' : 'false';
        }
        if (is_float($v) && floor($v) === $v) {
            $v = (int) $v;
        }

        return mb_strtolower(trim((string) $v));
    }
}
