/**
 * WASAYD RFID Bridge — WebSocket server
 *
 * Plan reference: Feature 1.1 (skeleton), Feature 3.1 (full implementation)
 * Plan file: /tmp/opencode/WASAYD-SPECKIT-IMPLEMENTATION-PLAN.md → Feature 3.1
 *
 * Architecture:
 *   RFID Reader Hardware (LLRP/Zebra REST)
 *       └── rfid-bridge (this service)
 *             ├── EPC deduplication (Redis sliding window)
 *             ├── Session management
 *             └── Redis Pub/Sub → Laravel queue workers
 */

'use strict';

const express = require('express');
const { WebSocketServer } = require('ws');
const Redis = require('ioredis');

const PORT   = process.env.BRIDGE_PORT   || 8765;
const SECRET = process.env.BRIDGE_SECRET || 'wasayd_rfid_secret_2024';

// ─── Redis connections ───────────────────────────────────────────────────────
const redis = new Redis({
  host: process.env.REDIS_HOST || 'localhost',
  port: parseInt(process.env.REDIS_PORT || '6379', 10),
  retryStrategy: (times) => Math.min(times * 50, 2000),
});

const redisPub = new Redis({
  host: process.env.REDIS_HOST || 'localhost',
  port: parseInt(process.env.REDIS_PORT || '6379', 10),
});

redis.on('connect',    () => console.log('[RFID Bridge] Redis connected'));
redis.on('error',      (e) => console.error('[RFID Bridge] Redis error:', e.message));
redisPub.on('connect', () => console.log('[RFID Bridge] Redis publisher connected'));

// ─── Express HTTP (health + REST ingestion) ──────────────────────────────────
const app = express();
app.use(express.json());

/** Health check — Plan T1.1-D */
app.get('/health', (req, res) => {
  const isRedisReady = redis.status === 'ready';
  res.status(isRedisReady ? 200 : 503).json({
    status:  isRedisReady ? 'ok' : 'degraded',
    service: 'rfid-bridge',
    version: '0.1.0',
    redis:   isRedisReady ? 'connected' : 'disconnected',
  });
});

/** REST endpoint for readers that POST reads (Zebra FX REST, polling mode) */
app.post('/reads', async (req, res) => {
  const { session_id, reader_id, company_id, warehouse_id, epcs } = req.body;
  if (!session_id || !epcs?.length) {
    return res.status(400).json({ error: 'session_id and epcs required' });
  }
  const now = Date.now();
  for (const epc of epcs) {
    await processEpcRead({ epc, session_id, reader_id, company_id, warehouse_id, ts: now });
  }
  res.json({ accepted: epcs.length });
});

const httpServer = app.listen(PORT, () => {
  console.log(`[RFID Bridge] HTTP server listening on :${PORT}`);
});

// ─── WebSocket server (LLRP real-time streaming) ─────────────────────────────
const wss = new WebSocketServer({ server: httpServer });

wss.on('connection', (ws, req) => {
  console.log('[RFID Bridge] WebSocket client connected');

  ws.on('message', async (data) => {
    try {
      const msg = JSON.parse(data.toString());
      if (msg.type === 'tag_read') {
        await processEpcRead(msg);
        // Echo to all UI subscribers
        broadcast({ type: 'tag_read', ...msg, server_ts: Date.now() });
      }
      if (msg.type === 'reader_status') {
        await redisPub.publish(
          `rfid:health:${msg.reader_id}`,
          JSON.stringify(msg)
        );
        broadcast(msg);
      }
    } catch (e) {
      console.error('[RFID Bridge] Message parse error:', e.message);
    }
  });

  ws.on('close', () => console.log('[RFID Bridge] WebSocket client disconnected'));
});

function broadcast(msg) {
  const payload = JSON.stringify(msg);
  wss.clients.forEach((client) => {
    if (client.readyState === 1) client.send(payload);
  });
}

// ─── EPC deduplication + pub/sub (Plan Feature 3.3) ─────────────────────────
/**
 * Lua script: sliding-window dedup per session per reader
 * Returns 1 = new unique EPC, 0 = duplicate within window
 * Plan reference: Feature 3.3 — EPC Deduplication (Redis Lua)
 */
const DEDUP_SCRIPT = `
local key    = KEYS[1]
local epc    = ARGV[1]
local now    = tonumber(ARGV[2])
local window = tonumber(ARGV[3])
local cutoff = now - window
redis.call('ZREMRANGEBYSCORE', key, '-inf', cutoff)
local count = redis.call('ZRANK', key, epc)
if count then return 0 end
redis.call('ZADD', key, now, epc)
redis.call('EXPIRE', key, math.ceil(window / 1000) + 5)
return 1
`;

const DEDUP_WINDOW_MS = parseInt(process.env.DEDUP_WINDOW_MS || '2000', 10);

async function processEpcRead({ epc, session_id, reader_id, company_id, warehouse_id, ts }) {
  if (!epc || !session_id) return;

  const key    = `dedup:${session_id}:${reader_id || 'unknown'}`;
  const isNew  = await redis.eval(DEDUP_SCRIPT, 1, key, epc, ts, DEDUP_WINDOW_MS);

  if (!isNew) return; // duplicate within window — discard

  const event = JSON.stringify({
    epc,
    session_id,
    reader_id,
    company_id,
    warehouse_id,
    ts,
    is_unique: true,
  });

  // Publish to company/warehouse channel for Laravel queue workers
  const channel = `rfid:${company_id || 'unknown'}:${warehouse_id || 'unknown'}`;
  await redisPub.publish(channel, event);

  // Also persist raw event to a stream for replay
  await redis.xadd(
    `rfid:stream:${session_id}`,
    '*',
    'epc', epc,
    'reader', reader_id || '',
    'ts', String(ts)
  );
}

console.log(`[RFID Bridge] WASAYD RFID Bridge v0.1.0 listening on :${PORT}`);
