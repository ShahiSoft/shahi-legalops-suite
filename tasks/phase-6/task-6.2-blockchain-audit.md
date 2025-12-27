# TASK 6.2: Blockchain Audit Trail (Optional)

Phase: 6 | Effort: 8-10h | Next: 6.3

Anchor consent/audit hashes to blockchain (optional toggle).
- Batch hashes (Merkle root) of consent/DSR/doc acceptance logs; write to public chain or testnet via API; store tx id, network, block height.
- Verification endpoint recomputes root for a batch/time window and compares to on-chain tx; include proof payload for a single record without leaking PII (hashed IDs).
- Opt-in per tenant; fallback to local integrity check if chain unreachable; retries with backoff; rate limits.

Success: hash recorded on chain; verification endpoint returns match + proof for a sample record; feature stays disabled unless toggled on.
