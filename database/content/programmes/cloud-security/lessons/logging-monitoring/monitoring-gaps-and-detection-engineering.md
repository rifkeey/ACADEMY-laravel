Detection is never finished. New services are adopted, attackers change behaviour, and logging silently breaks. **Detection engineering** is the discipline of deciding what to detect, building it, testing it and keeping it working.

## Find the gaps

Ask three questions regularly:

1. **Which sources are we missing?** Compare the log sources you collect against every account, subscription, project and service in use. New accounts and new services are the usual gaps.
2. **Which behaviours can't we detect?** Map your detections to the attacker behaviours relevant to cloud environments (the MITRE ATT&CK cloud matrix is a common reference) and look for empty areas.
3. **What would we have missed last time?** After each incident or exercise, list the events that should have alerted earlier.

A simple coverage table makes gaps visible:

| Account | Management events | Identity events | Data access (sensitive stores) | Flow logs | Provider threat detection |
| --- | --- | --- | --- | --- | --- |
| invoice-prod | ✔ | ✔ | ✔ | ✔ | ✔ |
| invoice-dev | ✔ | ✔ | ✘ | ✘ | ✔ |
| marketing-sandbox (new) | ✘ | ✘ | ✘ | ✘ | ✘ |

## Build detections as code

Treat detections like software:

- **Write them down** with a purpose, the data they use, the expected true-positive and the known benign cases. Formats such as **Sigma** let detection logic be shared across SIEMs.
- **Version-control** them and review changes.
- **Test** them against known-good and known-bad examples before deployment, and again after changes to log formats.
- **Measure** volume and accuracy (Network Security module 5, lesson 2) and tune.

## Monitor the monitoring

The worst monitoring gap is the one nobody notices. Alert when:

- a log source **stops sending** or drops sharply in volume;
- logging configuration is **changed or disabled** anywhere;
- provider detection services are **turned off** in any account;
- the SIEM's own data ingestion **fails**.

## Keep it proportionate

A small team with ten well-tested, high-value detections and complete log coverage is better protected than a large rule library nobody tunes. Start with the events that indicate impact or weakened controls (disabling logging, public exposure of sensitive data, privilege changes, root or global-admin use) and grow from there.

:::takeaway
Look for gaps in sources, behaviours and past incidents; build detections as documented, versioned and tested code; alert when logging itself breaks; and grow a small set of high-value detections steadily rather than collecting rules nobody maintains.
:::
