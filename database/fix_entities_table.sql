-- fix_entities_table.sql

USE personnel_tracking;

-- Add missing columns
ALTER TABLE inv_entities 
ADD COLUMN IF NOT EXISTS phone VARCHAR(20) AFTER tax_id,
ADD COLUMN IF NOT EXISTS email VARCHAR(100) AFTER phone,
ADD COLUMN IF NOT EXISTS address TEXT AFTER email;

-- Expand the type enum to include staff and both
ALTER TABLE inv_entities 
MODIFY COLUMN type ENUM('supplier', 'customer', 'both', 'staff') DEFAULT 'supplier';

-- Optionally remove contact_info if it's no longer needed, but let's keep it just in case.
