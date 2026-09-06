# BlueGate V4 Architecture

`3x-ui` is a provisioning provider, not the source of truth. PostgreSQL is the business source of truth.

Flow: User -> BlueGate API -> Order/Wallet/Payment -> Provisioning Queue -> Node Selector -> Provider Adapter -> 3x-ui -> Service -> Subscription Gateway.

Key rules:
1. Never expose 3x-ui credentials or direct panel subscription URLs to customers.
2. Store node credentials using Laravel encrypted casts.
3. Store only SHA-256 subscription token hashes plus an application pepper.
4. Wallet is ledger-first. Corrections are reversal transactions, not deletes.
5. Payment verification and provisioning are idempotent.
6. Web/API database must be isolated from VPN nodes.
7. Queue provisioning, usage sync, notifications and migration independently.
