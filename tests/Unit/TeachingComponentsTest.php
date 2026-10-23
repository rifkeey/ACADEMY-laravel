<?php

namespace Tests\Unit;

use App\Labs\IamPolicyEvaluator;
use App\Labs\LabEvaluator;
use App\Support\RichMarkdown;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** Callout/reveal Markdown, the IAM policy evaluator and written deliverables. */
class TeachingComponentsTest extends TestCase
{
    #[Test]
    public function teaching_blocks_render_and_stay_escaped(): void
    {
        $html = RichMarkdown::render("Intro\n\n:::reveal Show the <b>answer</b>\nRule **3** is shadowed. <script>alert(1)</script>\n:::\n\n:::warning\n[bad](javascript:alert(1))\n:::\n\n```text\n:::note not a block\n```");

        $this->assertStringContainsString('<details class="md-reveal"><summary>Show the &lt;b&gt;answer&lt;/b&gt;</summary>', $html);
        $this->assertStringContainsString('<strong>3</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('md-callout-warning', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString(':::note not a block', $html);     // fenced code is untouched
        $this->assertStringContainsString('<p>Intro</p>', $html);
    }

    #[Test]
    public function iam_policies_follow_deny_wins_and_implicit_deny(): void
    {
        $p = IamPolicyEvaluator::parse(json_encode(['Version' => '2012-10-17', 'Statement' => [
            ['Effect' => 'Allow', 'Action' => 's3:Get*', 'Resource' => 'arn:aws:s3:::kanem-invoices/*'],
            ['Effect' => 'Deny', 'Action' => 's3:GetObject', 'Resource' => 'arn:aws:s3:::kanem-invoices/private/*'],
        ]]));
        $this->assertTrue($p['ok']);
        $s = $p['statements'];

        $this->assertSame('allow', IamPolicyEvaluator::evaluate($s, 's3:GetObject', 'arn:aws:s3:::kanem-invoices/2026/inv-1.pdf')['decision']);
        $this->assertSame('allow', IamPolicyEvaluator::evaluate($s, 'S3:getobject', 'arn:aws:s3:::kanem-invoices/a')['decision']);    // actions are case-insensitive
        $this->assertSame('explicit-deny', IamPolicyEvaluator::evaluate($s, 's3:GetObject', 'arn:aws:s3:::kanem-invoices/private/x')['reason']);
        $this->assertSame('implicit-deny', IamPolicyEvaluator::evaluate($s, 's3:PutObject', 'arn:aws:s3:::kanem-invoices/a')['reason']);
        $this->assertSame('deny', IamPolicyEvaluator::evaluate($s, 's3:GetObject', 'arn:aws:s3:::Kanem-Invoices/a')['decision']);      // resources are case-sensitive

        $this->assertFalse(IamPolicyEvaluator::parse('{not json')['ok']);
        $this->assertStringContainsString('Condition', IamPolicyEvaluator::parse(json_encode(['Statement' => [['Effect' => 'Allow', 'Action' => '*', 'Resource' => '*', 'Condition' => []]]]))['error']);
        $this->assertTrue(IamPolicyEvaluator::parse(json_encode(['Statement' => ['Effect' => 'Allow', 'Action' => ['ec2:Describe*'], 'Resource' => '*']]))['ok']);   // single statement object
    }

    #[Test]
    public function iam_policy_and_deliverable_tasks_are_graded(): void
    {
        $content = ['tasks' => [
            ['id' => 'policy', 'points' => 4, 'input' => ['type' => 'iam-policy']],
            ['id' => 'memo', 'points' => 1, 'input' => ['type' => 'deliverable', 'minWords' => 8]],
        ]];
        $validator = ['tasks' => [
            'policy' => ['type' => 'iam-policy', 'feedback' => ['correct' => 'ok', 'incorrect' => 'no'], 'requests' => [
                ['label' => 'Read invoices', 'action' => 's3:GetObject', 'resource' => 'arn:aws:s3:::kanem-invoices/a.pdf', 'expect' => 'allow'],
                ['label' => 'Delete invoices', 'action' => 's3:DeleteObject', 'resource' => 'arn:aws:s3:::kanem-invoices/a.pdf', 'expect' => 'deny'],
            ]],
            'memo' => ['type' => 'deliverable', 'minWords' => 8, 'feedback' => ['correct' => 'ok', 'incorrect' => 'no'], 'mustMention' => [['label' => 'the deleted permission', 'any' => ['DeleteObject', 'delete']]]],
        ]];
        $good = json_encode(['Statement' => [['Effect' => 'Allow', 'Action' => 's3:GetObject', 'Resource' => 'arn:aws:s3:::kanem-invoices/*']]]);
        $tooBroad = json_encode(['Statement' => [['Effect' => 'Allow', 'Action' => 's3:*', 'Resource' => '*']]]);

        $r = LabEvaluator::evaluate($content, $validator, ['policy' => $good, 'memo' => 'I removed the s3:DeleteObject permission because invoices are read-only.'], 100, '2026-03-12T00:00:00Z');
        $this->assertTrue($r['passed']);

        $r = LabEvaluator::evaluate($content, $validator, ['policy' => $tooBroad, 'memo' => 'Too short.'], 100, '2026-03-12T00:00:00Z');
        $this->assertFalse($r['passed']);
        $this->assertStringContainsString('"Delete invoices" should be denied', $r['results'][0]['detail']);
        $this->assertStringContainsString('write at least 8', $r['results'][1]['detail']);

        $r = LabEvaluator::evaluate($content, $validator, ['policy' => $good, 'memo' => 'This memo is long enough but forgets the key change entirely.'], 100, '2026-03-12T00:00:00Z');
        $this->assertStringContainsString('does not yet cover: the deleted permission', $r['results'][1]['detail']);
    }
}
