# Out of range value for column 'crc32u'

## Problem

PHP 8.4 returns signed integer for `crc32()`. Negative values exceed `INT(10)` range, causing fatal MySQL errors.

## Fix

**1. Source files** — updated to use `sprintf('%u', crc32(...))`:

- `inc/a.import.inc.php:445,985`
- `inc/func.sql.inc.php:281`
- `gw_install/sql/install-structure.sql`
- `gw_install/sql/install-example.sql`
- `inc/query_storage_global-mysql410.php`

**2. Existing tables** — three-step migration:

```sql
-- Step 1: expand to bigint (handles negative values)
ALTER TABLE <table> MODIFY COLUMN crc32u bigint NOT NULL;

-- Step 2: convert negative → unsigned
UPDATE <table> SET crc32u = crc32u + 4294967296 WHERE crc32u < 0;

-- Step 3: shrink to unsigned int
ALTER TABLE <table> MODIFY COLUMN crc32u INT(10) UNSIGNED NOT NULL DEFAULT 0;
```

**3. Recalculate zero values** (MySQL 8.0+):

```sql
UPDATE <table>
SET crc32u = CAST(CONCAT('0x', HEX(CRC32(UPPER(term)))) AS UNSIGNED)
WHERE crc32u = 0;
```