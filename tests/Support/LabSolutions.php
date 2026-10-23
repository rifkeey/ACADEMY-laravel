<?php

namespace Tests\Support;

/** Reference answers proving every published lab can be passed with the content as written. */
final class LabSolutions
{
    public const ALL = [
        // SOC Analyst
        'authentication-log-investigation' => ['spray-ip' => '203.0.113.45', 'sprayed-count' => 14, 'compromised-user' => 'f.manager', 'pattern' => 'spray', 'ssh-outcome' => 'no'],
        'kql-suspicious-signins' => [
            'spray-ips' => 'SigninLogs | where ResultType != "0" | summarize Users = dcount(UserPrincipalName) by IPAddress | where Users >= 5 | project IPAddress',
            'brute-users' => 'SigninLogs | where ResultType != "0" | summarize n = count() by UserPrincipalName | where n > 10',
            'spray-success' => 'SigninLogs | where IPAddress == "203.0.113.45" and ResultType == "0" | project UserPrincipalName',
            'classify' => 'brute',
        ],
        'siem-alert-triage' => ['verdict' => 'tp', 'delivery-url-domain' => 'files.invoice-portal.example', 'dropped-binary' => 'updater.exe', 'evidence' => ['chain', 'interval', 'appdata'], 'first-action' => 'isolate'],
        'detection-rule-testing' => [
            'detection' => 'ProcessEvents | where ParentProcess in~ ("winword.exe", "excel.exe", "powerpnt.exe") and ProcessName in~ ("cmd.exe", "powershell.exe") | project EventId',
            'technique' => 't1204002', 'naive-fp' => 'it',
        ],
        'phishing-compromise-timeline' => [
            'order' => ['deliver' => ['step' => '1'], 'click' => ['step' => '2'], 'download' => ['step' => '3'], 'persist' => ['step' => '4'], 'c2' => ['step' => '5']],
            'persistence' => 'AdobeSyncHelper', 'c2-domain' => 'sync-telemetry.example', 'recipients' => ['p.etim', 'o.adamu', 'l.usman'], 'technique' => 't1566002',
        ],
        'soc-capstone-investigation' => [
            'account' => 'k.oyelaran', 'ip' => '192.0.2.199', 'persistence' => 'KanemTelemetryUpdate', 'exfil' => 'drop.filevault-share.example',
            'tactics' => ['credential-access', 'initial-access', 'persistence', 'collection', 'exfiltration'], 'severity' => 'critical', 'containment' => 'coordinated',
        ],
        // Governance, Risk and Compliance
        'risk-register' => ['register' => [
            'r1' => ['likelihood' => '5', 'impact' => '5', 'treatment' => 'treat'], 'r2' => ['likelihood' => '4', 'impact' => '5', 'treatment' => 'treat'],
            'r3' => ['likelihood' => '5', 'impact' => '1', 'treatment' => 'accept'], 'r4' => ['likelihood' => '3', 'impact' => '5', 'treatment' => 'transfer'],
            'r5' => ['likelihood' => '1', 'impact' => '1', 'treatment' => 'accept'],
        ]],
        'csf-control-mapping' => ['mapping' => [
            'c1' => ['fn' => 'GV'], 'c2' => ['fn' => 'ID'], 'c3' => ['fn' => 'PR'], 'c4' => ['fn' => 'DE'],
            'c5' => ['fn' => 'RS'], 'c6' => ['fn' => 'RC'], 'c7' => ['fn' => 'ID'], 'c8' => ['fn' => 'PR'],
        ]],
        'isms-scope-soa-review' => [
            'scope-issue' => 'excludes-service',
            'soa-review' => ['soa01' => ['verdict' => 'ok'], 'soa02' => ['verdict' => 'exclusion'], 'soa03' => ['verdict' => 'exclusion'], 'soa04' => ['verdict' => 'status'], 'soa05' => ['verdict' => 'ok'], 'soa06' => ['verdict' => 'justification'], 'soa07' => ['verdict' => 'ok'], 'soa08' => ['verdict' => 'status'], 'soa09' => ['verdict' => 'ok']],
            'untreated-risk' => 'R1', 'soa-fields' => ['applicable', 'justification', 'status'],
        ],
        'control-evidence-assessment' => [
            'early-provisioning' => 2, 'missing-ticket' => 'E1009', 'late-leavers' => ['E0942', 'E0958'],
            'evidence-quality' => ['ev1' => ['verdict' => 'accept', 'issue' => 'none'], 'ev2' => ['verdict' => 'reject', 'issue' => 'undated'], 'ev3' => ['verdict' => 'reject', 'issue' => 'period'], 'ev4' => ['verdict' => 'reject', 'issue' => 'assertion'], 'ev5' => ['verdict' => 'reject', 'issue' => 'requirement'], 'ev6' => ['verdict' => 'accept', 'issue' => 'none']],
            'ac02-conclusion' => 'operating', 'criteria' => 'criteria',
        ],
        'third-party-assessment' => [
            'tier' => 'critical', 'red-flags' => ['scope', 'subprocessor', 'notification', 'pentest'],
            'clauses' => ['cl1' => ['assessment' => 'adequate'], 'cl2' => ['assessment' => 'revise'], 'cl3' => ['assessment' => 'revise'], 'cl4' => ['assessment' => 'adequate'], 'cl5' => ['assessment' => 'adequate'], 'cl6' => ['assessment' => 'revise']],
            'role' => 'processor', 'cuec' => 'app-users', 'recommendation' => 'conditions',
        ],
        // Network Security
        'firewall-policy-builder' => [
            'excessive' => ['r2', 'r4', 'r7', 'r8', 'r10', 'r11'], 'shadowed' => 'r6', 'predict' => 'r4-allow', 'logging' => 'nolog', 'replace4' => 'narrow',
            'risks' => ['r2' => ['risk' => 'exposure'], 'r7' => ['risk' => 'lateral'], 'r8' => ['risk' => 'egress'], 'r10' => ['risk' => 'segmentation'], 'r11' => ['risk' => 'mgmt']],
            'rules' => [
                ['action' => 'allow', 'source' => 'internet', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 443],
                ['action' => 'allow', 'source' => 'users', 'destination' => 'internet', 'protocol' => 'tcp', 'port' => 443],
                ['action' => 'allow', 'source' => 'users', 'destination' => 'servers', 'protocol' => 'tcp', 'port' => 443],
                ['action' => 'allow', 'source' => 'dmz', 'destination' => 'servers', 'protocol' => 'tcp', 'port' => 5432],
                ['action' => 'allow', 'source' => 'management', 'destination' => 'servers', 'protocol' => 'tcp', 'port' => 22],
                ['action' => 'allow', 'source' => 'management', 'destination' => 'dmz', 'protocol' => 'tcp', 'port' => 22],
                ['action' => 'allow', 'source' => 'management', 'destination' => 'users', 'protocol' => 'tcp', 'port' => 3389],
                ['action' => 'allow', 'source' => 'servers', 'destination' => 'internet', 'protocol' => 'udp', 'port' => 53],
                ['action' => 'allow', 'source' => 'servers', 'destination' => 'internet', 'protocol' => 'tcp', 'port' => 443],
                ['action' => 'allow', 'source' => 'guest', 'destination' => 'internet', 'protocol' => 'tcp', 'port' => 80],
                ['action' => 'allow', 'source' => 'guest', 'destination' => 'internet', 'protocol' => 'tcp', 'port' => 443],
            ],
            'change' => 'Summary: retire rules 2, 4 and 8 on FW-HQ-01 and deploy the reviewed least-privilege policy. Rules affected: rule 2 (any to dmz, all ports) is removed; rule 4 (dmz to servers, any TCP) is replaced by a single logged rule from the web server to the loans database on TCP 5432; rule 8 (servers to internet, any) is replaced by DNS and HTTPS only. Business justification: the internal audit found the DMZ and server zone exposed, and the night-time SSH from the web server must be investigated. Business impact: the website and patching could be affected if a dependency was missed, so the change happens in the Sunday 02:00 window with digital channels on call. Testing and validation: run each required test connection, check hit counters on the new rules and review the deny log daily for seven days. Rollback plan: restore the saved configuration and re-enable the old rules within fifteen minutes if any business flow fails. Approval requested from the change board, the CIO and the rule owners.',
        ],
        'packet-and-dns-analysis' => ['handshake' => ['1', '2', '3'], 'rtt' => '31', 'retransmission' => '6', 'tunnel-host' => '10.20.4.57', 'tunnel-domain' => 'stat-relay.example'],
        // Cloud Security
        'iam-excessive-permissions' => [
            'excessive' => ['S2', 'S4', 'S6', 'S7'], 'escalation' => 's4s6', 'evasion' => 'S7', 'evidence' => 'incident', 'credentials' => 'role',
            'policy' => '{"Version":"2012-10-17","Statement":[{"Effect":"Allow","Action":"s3:GetObject","Resource":"arn:aws:s3:::kanem-invoices/*"},{"Effect":"Allow","Action":"s3:PutObject","Resource":"arn:aws:s3:::kanem-invoices/generated/*"},{"Effect":"Allow","Action":"kms:Decrypt","Resource":"arn:aws:kms:eu-west-1:111122223333:key/0000aaaa-11bb-22cc-33dd-444455556666"},{"Effect":"Allow","Action":["logs:CreateLogStream","logs:PutLogEvents"],"Resource":"arn:aws:logs:eu-west-1:111122223333:log-group:/kanem/invoice-app:*"},{"Effect":"Deny","Action":"s3:DeleteObject","Resource":"arn:aws:s3:::kanem-invoices/*"}]}',
            'finding' => 'Summary: the invoice application identity can make itself an administrator. It holds iam:PassRole on every role plus full EC2 access, can read and delete every S3 bucket including payroll, and can stop CloudTrail. Evidence: on 28 February at 02:41 UTC the identity launched an instance and passed the kanem-admin role from 203.0.113.50, an external address the application never uses, so this should be escalated as a security incident today. Recommended actions: deactivate the access key now, attach a role with the least-privilege policy to the application compute, investigate CloudTrail for 28 February and every action taken by kanem-admin, terminate the unknown instance after preserving evidence, and add an organisation guardrail so audit logging cannot be stopped. Still to check: other identities with PassRole, bucket policies on kanem-invoices, and whether any invoice or payroll data left the account. I will report progress to the security manager by the end of the day and update the incident record as evidence is reviewed.',
        ],
        'cloud-audit-log-investigation' => [
            'identity' => 'dev.akande', 'events' => 'CloudAudit | where SourceIP == "198.51.100.140" | project EventName',
            'persistence' => 'CreateAccessKey', 'containment' => 'contain',
        ],
    ];
}
