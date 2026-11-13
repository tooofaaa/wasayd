-- WASAYD PostgreSQL initialization
-- Plan ref: Feature 1.1, ADR-002 (PostgreSQL required)

-- Enable extensions required by the plan
CREATE EXTENSION IF NOT EXISTS "pgcrypto";   -- gen_random_uuid(), SHA-256 chain
CREATE EXTENSION IF NOT EXISTS "pg_trgm";   -- trigram full-text item search
CREATE EXTENSION IF NOT EXISTS "btree_gin"; -- GIN on btree-compatible types
CREATE EXTENSION IF NOT EXISTS "unaccent";  -- accent-insensitive Arabic/English search

-- App role with restricted permissions (Constitution principle: IMMUTABILITY-FIRST)
-- inventory_transactions and audit_logs will have UPDATE/DELETE revoked after migration
DO $$
BEGIN
    IF NOT EXISTS (SELECT FROM pg_catalog.pg_roles WHERE rolname = 'wasayd_app') THEN
        CREATE ROLE wasayd_app LOGIN PASSWORD 'wasayd_app_2024';
    END IF;
END
$$;

GRANT ALL PRIVILEGES ON DATABASE wasayd TO wasayd;
