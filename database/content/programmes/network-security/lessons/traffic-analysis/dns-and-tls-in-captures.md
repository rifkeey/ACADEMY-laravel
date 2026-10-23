Almost every session starts with DNS and, for web traffic, continues with a TLS handshake. Both leave useful, readable information in a capture even when the application data is encrypted.

## DNS in a capture

| Field | Filter | Shows |
| --- | --- | --- |
| Query name | `dns.qry.name` | What the client looked up |
| Query type | `dns.qry.type` | A (IPv4), AAAA (IPv6), TXT, MX… |
| Response code | `dns.flags.rcode` | 0 = no error, 3 = no such name (NXDOMAIN) |
| Answer | `dns.a` | The address returned |
| Response time | `dns.time` | How long the resolver took to answer |

Wireshark links each response to its query, so you can see slow or failed lookups at a glance. A slow DNS answer delays everything after it. A lookup that returns the **wrong** or an **old** address sends the client to the wrong server, which is a common cause of problems after a migration.

## TLS in a capture

A TLS 1.3 handshake (RFC 8446) looks like this:

| Frame | From | Message | Readable? |
| --- | --- | --- | --- |
| 1 | client | **Client Hello**: supported versions and cipher suites, and the **server name (SNI)** | Yes |
| 2 | server | **Server Hello**: chosen version and cipher suite | Yes |
| 2 (cont.) | server | Change Cipher Spec (for compatibility), then encrypted handshake messages including the certificate | Certificate **not** readable in TLS 1.3 |
| 3… | both | Application Data | No |

Useful filters:

```text
tls.handshake.type == 1                    Client Hello
tls.handshake.extensions_server_name       the SNI value
tls.handshake.type == 2                    Server Hello
tls.alert_message                          TLS alerts (handshake failures)
```

:::note Which version was negotiated?
In TLS 1.3 the record header still says TLS 1.2 for compatibility. The real version is in the Server Hello's **supported_versions** extension. Wireshark shows the result in the Protocol column as TLSv1.3.
:::

## What you can and cannot learn from encrypted traffic

| You can see | You cannot see (without decryption) |
| --- | --- |
| Who talked to whom, when, for how long, how much | URLs, headers, form data |
| Server name (SNI) | Page content or files |
| TLS version and cipher suite | The certificate (in TLS 1.3) |
| Handshake failures and alerts | Application errors |
| Timing: handshake speed, gaps, retransmissions | |

This is often enough: in many performance cases the timing tells the whole story. Where content is needed, decryption must be planned and authorised (module 5, lesson 5), for example by using the server's logs or an authorised inspection point.

:::takeaway
DNS shows what was looked up, what came back and how fast. The TLS Client Hello shows the server name and offered versions, and the Server Hello shows what was negotiated. Even with encrypted content, timing and metadata often explain the problem.
:::
