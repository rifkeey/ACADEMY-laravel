// Builds the synthetic capture for Network module 8 ("the portal is slow").
// Writes a classic libpcap file (format: draft-ietf-opsawg-pcap, still an Internet-Draft) with Ethernet,
// IPv4, UDP/DNS and TCP carrying a TLS 1.3 handshake. Encrypted records hold seeded pseudo-random bytes,
// so the output is identical on every run. Returns the packet list shown in the lab.
const fs = require('fs');
const crypto = require('crypto');

// Deterministic bytes (mulberry32) so reruns produce the same file and hash.
function prng(seed) {
    let a = seed >>> 0;
    return (n) => {
        const out = Buffer.alloc(n);
        for (let i = 0; i < n; i++) {
            a = (a + 0x6d2b79f5) >>> 0;
            let t = a;
            t = Math.imul(t ^ (t >>> 15), t | 1);
            t ^= t + Math.imul(t ^ (t >>> 7), t | 61);
            out[i] = ((t ^ (t >>> 14)) >>> 0) & 0xff;
        }
        return out;
    };
}
let rand = prng(20260310);

const u16 = (n) => { const b = Buffer.alloc(2); b.writeUInt16BE(n); return b; };
const u24 = (n) => Buffer.from([(n >> 16) & 0xff, (n >> 8) & 0xff, n & 0xff]);
const u32 = (n) => { const b = Buffer.alloc(4); b.writeUInt32BE(n >>> 0); return b; };
const ip = (s) => Buffer.from(s.split('.').map(Number));
const mac = (s) => Buffer.from(s.split(':').map((h) => parseInt(h, 16)));

function checksum(buf) {
    let sum = 0;
    for (let i = 0; i < buf.length; i += 2) sum += (buf[i] << 8) + (i + 1 < buf.length ? buf[i + 1] : 0);
    while (sum >> 16) sum = (sum & 0xffff) + (sum >> 16);
    return (~sum) & 0xffff;
}

const HOSTS = {
    client: { ip: '10.40.9.21', mac: '3c:52:82:aa:12:21' },
    resolver: { ip: '10.40.8.53', mac: '00:1b:21:0a:08:01' },   // reached via the gateway; same MAC as gateway below
    portal: { ip: '10.10.0.15', mac: '00:1b:21:0a:08:01' },
    static: { ip: '10.10.0.16', mac: '00:1b:21:0a:08:01' },
};
const GATEWAY_MAC = '00:1b:21:0a:08:01';
let ipId = 0x3100;

function ipv4(src, dst, proto, payload) {
    const h = Buffer.concat([Buffer.from([0x45, 0]), u16(20 + payload.length), u16(ipId++), u16(0x4000), Buffer.from([64, proto]), u16(0), ip(src), ip(dst)]);
    h.writeUInt16BE(checksum(h), 10);
    return Buffer.concat([h, payload]);
}
function pseudo(src, dst, proto, len) {
    return Buffer.concat([ip(src), ip(dst), Buffer.from([0, proto]), u16(len)]);
}
function udp(src, dst, sport, dport, data) {
    const seg = Buffer.concat([u16(sport), u16(dport), u16(8 + data.length), u16(0), data]);
    seg.writeUInt16BE(checksum(Buffer.concat([pseudo(src, dst, 17, seg.length), seg])) || 0xffff, 6);
    return ipv4(src, dst, 17, seg);
}
const FLAG = { FIN: 0x01, SYN: 0x02, RST: 0x04, PSH: 0x08, ACK: 0x10 };
function tcp(src, dst, sport, dport, seq, ack, flags, win, data = Buffer.alloc(0), mss = 0) {
    const opts = mss ? Buffer.concat([Buffer.from([2, 4]), u16(mss)]) : Buffer.alloc(0);
    const off = (20 + opts.length) / 4;
    const seg = Buffer.concat([u16(sport), u16(dport), u32(seq), u32(ack), Buffer.from([off << 4, flags]), u16(win), u16(0), u16(0), opts, data]);
    seg.writeUInt16BE(checksum(Buffer.concat([pseudo(src, dst, 6, seg.length), seg])), 16);
    return ipv4(src, dst, 6, seg);
}
function ether(srcMac, dstMac, ipPacket) {
    return Buffer.concat([mac(dstMac), mac(srcMac), u16(0x0800), ipPacket]);
}

// DNS
const qname = (name) => Buffer.concat([...name.split('.').map((l) => Buffer.concat([Buffer.from([l.length]), Buffer.from(l, 'ascii')])), Buffer.from([0])]);
const dnsQuery = (id, name) => Buffer.concat([u16(id), u16(0x0100), u16(1), u16(0), u16(0), u16(0), qname(name), u16(1), u16(1)]);
const dnsAnswer = (id, name, addr) => Buffer.concat([u16(id), u16(0x8180), u16(1), u16(1), u16(0), u16(0), qname(name), u16(1), u16(1), u16(0xc00c), u16(1), u16(1), u32(300), u16(4), ip(addr)]);

// TLS 1.3
const ext = (type, body) => Buffer.concat([u16(type), u16(body.length), body]);
const record = (type, body) => Buffer.concat([Buffer.from([type]), u16(type === 0x16 && body[0] === 1 ? 0x0301 : 0x0303), u16(body.length), body]);
const handshake = (type, body) => Buffer.concat([Buffer.from([type]), u24(body.length), body]);
let sessionId;
function clientHello(sni) {
    const name = Buffer.from(sni, 'ascii');
    const exts = Buffer.concat([
        ext(0x0000, Buffer.concat([u16(name.length + 3), Buffer.from([0]), u16(name.length), name])),
        ext(0x000a, Buffer.concat([u16(4), u16(0x001d), u16(0x0017)])),
        ext(0x000d, Buffer.concat([u16(6), u16(0x0804), u16(0x0403), u16(0x0805)])),
        ext(0x002b, Buffer.concat([Buffer.from([4]), u16(0x0304), u16(0x0303)])),
        ext(0x0033, Buffer.concat([u16(36), u16(0x001d), u16(32), rand(32)])),
    ]);
    const body = Buffer.concat([u16(0x0303), rand(32), Buffer.from([32]), sessionId, u16(6), u16(0x1301), u16(0x1302), u16(0x1303), Buffer.from([1, 0]), u16(exts.length), exts]);
    return record(0x16, handshake(1, body));
}
function serverHello() {
    const exts = Buffer.concat([ext(0x002b, u16(0x0304)), ext(0x0033, Buffer.concat([u16(0x001d), u16(32), rand(32)]))]);
    const body = Buffer.concat([u16(0x0303), rand(32), Buffer.from([32]), sessionId, u16(0x1301), Buffer.from([0]), u16(exts.length), exts]);
    return record(0x16, handshake(2, body));
}
const ccs = () => record(0x14, Buffer.from([1]));
const appData = (n) => record(0x17, rand(n));

/** Builds the capture. Returns { file buffer, packets for the lab, frame numbers by tag }. */
function build() {
    rand = prng(20260310);
    sessionId = rand(32);
    ipId = 0x3100;
    const C = HOSTS.client.ip, R = HOSTS.resolver.ip, P = HOSTS.portal.ip, S = HOSTS.static.ip;
    const start = Date.UTC(2026, 2, 10, 9, 14, 0) * 1000 + 512300;   // microseconds
    const frames = [];
    const add = (t, from, to, ipPacket, proto, info, tag) => {
        const out = from === 'client';
        frames.push({ t, data: ether(out ? HOSTS.client.mac : GATEWAY_MAC, out ? GATEWAY_MAC : HOSTS.client.mac, ipPacket), src: HOSTS[from].ip, dst: HOSTS[to].ip, proto, info, tag });
    };

    // DNS for the portal
    add(0.000000, 'client', 'resolver', udp(C, R, 53001, 53, dnsQuery(0x1a2b, 'portal.kanem.example')), 'DNS', 'Standard query 0x1a2b A portal.kanem.example', 'dns1q');
    add(0.002100, 'resolver', 'client', udp(R, C, 53, 53001, dnsAnswer(0x1a2b, 'portal.kanem.example', P)), 'DNS', 'Standard query response 0x1a2b A portal.kanem.example A 10.10.0.15', 'dns1r');

    // TCP + TLS to the portal (client port 52114)
    const ci = 0x5e1f2a10, si = 0x9a33c0d4;
    const ch = clientHello('portal.kanem.example');
    const sflight = Buffer.concat([serverHello(), ccs(), appData(1020)]);
    const cfin = Buffer.concat([ccs(), appData(53), appData(214)]);
    const L1 = ch.length, L2 = sflight.length, L3 = cfin.length;
    const cw = 64240, sw = 65160;
    add(0.002600, 'client', 'portal', tcp(C, P, 52114, 443, ci, 0, FLAG.SYN, cw, undefined, 1460), 'TCP', `52114 → 443 [SYN] Seq=0 Win=${cw} Len=0 MSS=1460`, 'syn');
    add(0.040600, 'portal', 'client', tcp(P, C, 443, 52114, si, ci + 1, FLAG.SYN | FLAG.ACK, sw, undefined, 1460), 'TCP', `443 → 52114 [SYN, ACK] Seq=0 Ack=1 Win=${sw} Len=0 MSS=1460`, 'synack');
    add(0.040700, 'client', 'portal', tcp(C, P, 52114, 443, ci + 1, si + 1, FLAG.ACK, cw), 'TCP', `52114 → 443 [ACK] Seq=1 Ack=1 Win=${cw} Len=0`, 'ack');
    add(0.041200, 'client', 'portal', tcp(C, P, 52114, 443, ci + 1, si + 1, FLAG.PSH | FLAG.ACK, cw, ch), 'TLSv1.3', 'Client Hello (SNI=portal.kanem.example)', 'ch');
    add(0.080300, 'portal', 'client', tcp(P, C, 443, 52114, si + 1, ci + 1 + L1, FLAG.PSH | FLAG.ACK, sw, sflight), 'TLSv1.3', 'Server Hello, Change Cipher Spec, Application Data', 'sh');
    add(0.080400, 'client', 'portal', tcp(C, P, 52114, 443, ci + 1 + L1, si + 1 + L2, FLAG.ACK, cw), 'TCP', `52114 → 443 [ACK] Seq=${1 + L1} Ack=${1 + L2} Win=${cw} Len=0`);
    add(0.081000, 'client', 'portal', tcp(C, P, 52114, 443, ci + 1 + L1, si + 1 + L2, FLAG.PSH | FLAG.ACK, cw, cfin), 'TLSv1.3', 'Change Cipher Spec, Application Data, Application Data', 'req');
    const cseq = ci + 1 + L1 + L3, cs = 1 + L1 + L3;
    add(0.119500, 'portal', 'client', tcp(P, C, 443, 52114, si + 1 + L2, cseq, FLAG.ACK, sw), 'TCP', `443 → 52114 [ACK] Seq=${1 + L2} Ack=${cs} Win=${sw} Len=0`);

    // Server response in four 1400-byte segments; the first is lost before the capture point.
    const A = si + 1 + L2, a = 1 + L2;
    const seg = () => appData(1395);
    const lost = seg();
    const s2 = seg(), s3 = seg(), s4 = seg();
    add(0.150100, 'portal', 'client', tcp(P, C, 443, 52114, A + 1400, cseq, FLAG.PSH | FLAG.ACK, sw, s2), 'TLSv1.3', '[TCP Previous segment not captured] Application Data', 'gap');
    add(0.150200, 'client', 'portal', tcp(C, P, 52114, 443, cseq, A, FLAG.ACK, cw), 'TCP', `[TCP Dup ACK #1] 52114 → 443 [ACK] Seq=${cs} Ack=${a} Win=${cw} Len=0`, 'dup1');
    add(0.150300, 'portal', 'client', tcp(P, C, 443, 52114, A + 2800, cseq, FLAG.PSH | FLAG.ACK, sw, s3), 'TLSv1.3', 'Application Data');
    add(0.150400, 'client', 'portal', tcp(C, P, 52114, 443, cseq, A, FLAG.ACK, cw), 'TCP', `[TCP Dup ACK #2] 52114 → 443 [ACK] Seq=${cs} Ack=${a} Win=${cw} Len=0`);
    add(0.150500, 'portal', 'client', tcp(P, C, 443, 52114, A + 4200, cseq, FLAG.PSH | FLAG.ACK, sw, s4), 'TLSv1.3', 'Application Data');
    add(0.150600, 'client', 'portal', tcp(C, P, 52114, 443, cseq, A, FLAG.ACK, cw), 'TCP', `[TCP Dup ACK #3] 52114 → 443 [ACK] Seq=${cs} Ack=${a} Win=${cw} Len=0`);
    add(0.189000, 'portal', 'client', tcp(P, C, 443, 52114, A, cseq, FLAG.PSH | FLAG.ACK, sw, lost), 'TCP', `[TCP Fast Retransmission] 443 → 52114 [PSH, ACK] Seq=${a} Ack=${cs} Win=${sw} Len=1400`, 'retrans');
    add(0.189100, 'client', 'portal', tcp(C, P, 52114, 443, cseq, A + 5600, FLAG.ACK, cw), 'TCP', `52114 → 443 [ACK] Seq=${cs} Ack=${a + 5600} Win=${cw} Len=0`);

    // The page also needs static.kanem.example, which now resolves to the migrated server.
    add(0.200000, 'client', 'resolver', udp(C, R, 53002, 53, dnsQuery(0x1a2c, 'static.kanem.example')), 'DNS', 'Standard query 0x1a2c A static.kanem.example', 'dns2q');
    add(0.201900, 'resolver', 'client', udp(R, C, 53, 53002, dnsAnswer(0x1a2c, 'static.kanem.example', S)), 'DNS', 'Standard query response 0x1a2c A static.kanem.example A 10.10.0.16', 'dns2r');
    const xi = 0x22c4e801;
    add(0.202300, 'client', 'static', tcp(C, S, 52120, 443, xi, 0, FLAG.SYN, cw, undefined, 1460), 'TCP', `52120 → 443 [SYN] Seq=0 Win=${cw} Len=0 MSS=1460`, 'ssyn');
    add(1.203300, 'client', 'static', tcp(C, S, 52120, 443, xi, 0, FLAG.SYN, cw, undefined, 1460), 'TCP', `[TCP Retransmission] 52120 → 443 [SYN] Seq=0 Win=${cw} Len=0 MSS=1460`, 'ssyn2');
    add(3.205300, 'client', 'static', tcp(C, S, 52120, 443, xi, 0, FLAG.SYN, cw, undefined, 1460), 'TCP', `[TCP Retransmission] 52120 → 443 [SYN] Seq=0 Win=${cw} Len=0 MSS=1460`, 'ssyn3');
    add(7.209300, 'client', 'static', tcp(C, S, 52120, 443, xi, 0, FLAG.SYN, cw, undefined, 1460), 'TCP', `[TCP Retransmission] 52120 → 443 [SYN] Seq=0 Win=${cw} Len=0 MSS=1460`, 'ssyn4');

    // The browser gives up on the static server and the user closes the page.
    add(9.500000, 'client', 'portal', tcp(C, P, 52114, 443, cseq, A + 5600, FLAG.FIN | FLAG.ACK, cw), 'TCP', `52114 → 443 [FIN, ACK] Seq=${cs} Ack=${a + 5600} Win=${cw} Len=0`, 'fin');
    add(9.538000, 'portal', 'client', tcp(P, C, 443, 52114, A + 5600, cseq + 1, FLAG.FIN | FLAG.ACK, sw), 'TCP', `443 → 52114 [FIN, ACK] Seq=${a + 5600} Ack=${cs + 1} Win=${sw} Len=0`);
    add(9.538100, 'client', 'portal', tcp(C, P, 52114, 443, cseq + 1, A + 5601, FLAG.ACK, cw), 'TCP', `52114 → 443 [ACK] Seq=${cs + 1} Ack=${a + 5601} Win=${cw} Len=0`);

    // libpcap file: global header (microsecond magic, v2.4, snaplen 65535, LINKTYPE_ETHERNET) then records.
    const parts = [Buffer.from('d4c3b2a1', 'hex'), Buffer.from([2, 0, 4, 0]), Buffer.alloc(8), Buffer.from([0xff, 0xff, 0, 0]), Buffer.from([1, 0, 0, 0])];
    const frameNo = {};
    const packets = frames.map((f, i) => {
        const us = start + Math.round(f.t * 1e6);
        const hdr = Buffer.alloc(16);
        hdr.writeUInt32LE(Math.floor(us / 1e6), 0);
        hdr.writeUInt32LE(us % 1e6, 4);
        hdr.writeUInt32LE(f.data.length, 8);
        hdr.writeUInt32LE(f.data.length, 12);
        parts.push(hdr, f.data);
        if (f.tag) frameNo[f.tag] = i + 1;
        return { No: i + 1, Time: f.t.toFixed(6), Source: f.src, Destination: f.dst, Protocol: f.proto, Length: f.data.length, Info: f.info };
    });
    const file = Buffer.concat(parts);
    return { file, packets, frameNo, sha256: crypto.createHash('sha256').update(file).digest('hex') };
}

/** Re-reads a pcap buffer and checks every IPv4, TCP and UDP checksum. Throws on any mismatch. */
function verify(file) {
    if (file.readUInt32LE(0) !== 0xa1b2c3d4 || file.readUInt32LE(20) !== 1) throw new Error('bad pcap header');
    let off = 24, n = 0;
    while (off < file.length) {
        const len = file.readUInt32LE(off + 8);
        const frame = file.subarray(off + 16, off + 16 + len);
        const iph = frame.subarray(14, 34);
        if (checksum(iph) !== 0) throw new Error(`IPv4 checksum, frame ${n + 1}`);
        const proto = iph[9], total = iph.readUInt16BE(2);
        const l4 = frame.subarray(34, 14 + total);
        if (checksum(Buffer.concat([iph.subarray(12, 20), Buffer.from([0, proto]), u16(l4.length), l4])) !== 0) throw new Error(`L4 checksum, frame ${n + 1}`);
        off += 16 + len;
        n++;
    }
    return n;
}

module.exports = { build, verify };

if (require.main === module) {
    const out = process.argv[2];
    const { file, packets, sha256 } = build();
    console.log('frames:', verify(file), 'of', packets.length, 'sha256:', sha256);
    if (out) fs.writeFileSync(out, file);
}
