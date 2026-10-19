<?php

namespace Tests\Unit;

use App\Assessment\Grading;
use App\Labs\FirewallEvaluator;
use App\Labs\Kql\Kql;
use App\Labs\Kql\KqlError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/** Same cases as the original TypeScript lab-engine tests, so behaviour is proven unchanged. */
class LabEngineTest extends TestCase
{
    private const NOW = '2026-03-04T06:00:00Z';

    private function signins(): array
    {
        return [[
            'name' => 'SigninLogs', 'description' => 'test',
            'columns' => ['TimeGenerated', 'UserPrincipalName', 'IPAddress', 'ResultType'],
            'rows' => [
                ['TimeGenerated' => '2026-03-04T01:00:00Z', 'UserPrincipalName' => 'a@x.example', 'IPAddress' => '203.0.113.45', 'ResultType' => '50126'],
                ['TimeGenerated' => '2026-03-04T01:01:00Z', 'UserPrincipalName' => 'b@x.example', 'IPAddress' => '203.0.113.45', 'ResultType' => '50126'],
                ['TimeGenerated' => '2026-03-04T01:02:00Z', 'UserPrincipalName' => 'c@x.example', 'IPAddress' => '203.0.113.45', 'ResultType' => '50126'],
                ['TimeGenerated' => '2026-03-04T01:03:00Z', 'UserPrincipalName' => 'c@x.example', 'IPAddress' => '203.0.113.45', 'ResultType' => '0'],
                ['TimeGenerated' => '2026-03-04T05:00:00Z', 'UserPrincipalName' => 'a@x.example', 'IPAddress' => '10.0.0.5', 'ResultType' => '0'],
            ],
        ]];
    }

    private function kql(string $q): array
    {
        return Kql::run($q, $this->signins(), self::NOW);
    }

    #[Test]
    public function it_filters_summarises_and_orders(): void
    {
        $r = $this->kql('SigninLogs | where ResultType != "0" | summarize Failures = count(), Users = dcount(UserPrincipalName) by IPAddress | order by Users desc');
        $this->assertSame(['IPAddress', 'Failures', 'Users'], $r['columns']);
        $this->assertSame([['IPAddress' => '203.0.113.45', 'Failures' => 3, 'Users' => 3]], $r['rows']);
    }

    #[Test]
    public function it_supports_ago_datetime_and_bin(): void
    {
        $this->assertSame(1, $this->kql('SigninLogs | where TimeGenerated > ago(2h) | count')['rows'][0]['Count']);
        $this->assertSame(2, $this->kql('SigninLogs | where TimeGenerated < datetime(2026-03-04T01:02:00Z) | count')['rows'][0]['Count']);
        $b = $this->kql('SigninLogs | summarize n = count() by bin(TimeGenerated, 1h) | order by TimeGenerated asc');
        $this->assertSame([4, 1], array_column($b['rows'], 'n'));
        $this->assertSame('2026-03-04T01:00:00.000Z', $b['rows'][0]['TimeGenerated']);
    }

    #[Test]
    public function it_handles_case_sensitive_and_insensitive_operators(): void
    {
        $this->assertSame(0, $this->kql('SigninLogs | where UserPrincipalName == "A@x.example" | count')['rows'][0]['Count']);
        $this->assertSame(2, $this->kql('SigninLogs | where UserPrincipalName =~ "A@x.example" | count')['rows'][0]['Count']);
        $this->assertSame(2, $this->kql('SigninLogs | where UserPrincipalName in~ ("A@X.EXAMPLE") | count')['rows'][0]['Count']);
        $this->assertSame(3, $this->kql('SigninLogs | where UserPrincipalName !in ("a@x.example") | count')['rows'][0]['Count']);
        $this->assertSame([['UserPrincipalName' => 'c@x.example']], $this->kql('SigninLogs | where IPAddress startswith "203." and ResultType == "0" | project UserPrincipalName')['rows']);
    }

    #[Test]
    public function it_supports_distinct_top_extend_and_aliases(): void
    {
        $this->assertCount(2, $this->kql('SigninLogs | distinct IPAddress')['rows']);
        $this->assertSame('10.0.0.5', $this->kql('SigninLogs | top 1 by TimeGenerated')['rows'][0]['IPAddress']);
        $e = $this->kql('SigninLogs | extend Upper = toupper(IPAddress) | project Who = UserPrincipalName, Upper | take 1');
        $this->assertSame(['Who', 'Upper'], $e['columns']);
        $this->assertSame('["a@x.example","b@x.example","c@x.example"]', $this->kql('SigninLogs | summarize make_set(UserPrincipalName)')['rows'][0]['set_UserPrincipalName']);
    }

    #[Test]
    public function it_gives_helpful_errors(): void
    {
        foreach ([
            ['signinlogs | take 1', "Did you mean 'SigninLogs'"],
            ['SigninLogs | where userprincipalname == "x"', 'case-sensitive'],
            ['SigninLogs | join kind=inner (SigninLogs) on IPAddress', 'not supported'],
            ['SigninLogs | where "unterminated', 'Unterminated'],
        ] as [$q, $msg]) {
            try {
                $this->kql($q);
                $this->fail("Expected an error for: {$q}");
            } catch (KqlError $e) {
                $this->assertStringContainsString($msg, $e->getMessage());
            }
        }
    }

    #[Test]
    public function firewall_uses_first_match_and_default_deny(): void
    {
        $rules = FirewallEvaluator::sanitize([
            ['action' => 'deny', 'source' => 'guest', 'destination' => 'any', 'protocol' => 'any', 'port' => 'any'],
            ['action' => 'allow', 'source' => 'any', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 443],
            ['action' => 'bogus'],
        ], ['guest', 'dmz', 'internet'], 10);
        $this->assertCount(2, $rules);
        $this->assertSame('deny', FirewallEvaluator::evaluate($rules, ['source' => 'guest', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 443])['action']);
        $this->assertSame('allow', FirewallEvaluator::evaluate($rules, ['source' => 'internet', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 443])['action']);
        $this->assertSame(['action' => 'deny', 'ruleIndex' => null], FirewallEvaluator::evaluate($rules, ['source' => 'internet', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 22]));
    }

    #[Test]
    public function pass_mark_is_integer_exact(): void
    {
        $this->assertFalse(Grading::isPass(37, 50, 75));   // 74% fails
        $this->assertTrue(Grading::isPass(30, 40, 75));    // 75% passes
        $this->assertFalse(Grading::isPass(29, 40, 75));
        $this->assertFalse(Grading::isPass(0, 0, 75));
        $this->assertTrue(Grading::isAnswerCorrect([3, 1], [1, 3]));
        $this->assertFalse(Grading::isAnswerCorrect([3, 1], [1]));
        $this->assertFalse(Grading::isAnswerCorrect([3], [3, 4]));
    }

    #[Test]
    public function balanced_selection_covers_categories_without_repeats(): void
    {
        $pool = array_map(fn ($i) => ['id' => $i, 'category' => 'c'.($i % 9)], range(1, 45));
        $picked = Grading::selectBalanced($pool, 40);
        $this->assertCount(40, array_unique(array_column($picked, 'id')));
        $perCategory = array_count_values(array_column($picked, 'category'));
        $this->assertGreaterThanOrEqual(4, min($perCategory));
    }
}
