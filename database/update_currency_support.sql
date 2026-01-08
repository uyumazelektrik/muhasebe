-- Faz 7: Çoklu Para Birimi ve Varlık Takibi için Veritabanı Güncellemesi - Düzeltilmiş Versiyon

-- 1. Kur Geçmişi Tablosu
CREATE TABLE IF NOT EXISTS inv_currency_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    currency_code VARCHAR(10) NOT NULL,
    rate DECIMAL(15,4) NOT NULL,
    source VARCHAR(50) DEFAULT 'TCMB',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_currency_date (currency_code, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. Varlık Bazlı Bakiye Tablosu
CREATE TABLE IF NOT EXISTS inv_entity_balances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    entity_id INT NOT NULL,
    asset_type ENUM('TL', 'USD', 'EUR', 'GOLD', 'CREDIT_CARD') NOT NULL DEFAULT 'TL',
    amount DECIMAL(15,4) DEFAULT 0.0000,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_entity_asset (entity_id, asset_type),
    FOREIGN KEY (entity_id) REFERENCES inv_entities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 3. İşlem Tablosuna Para Birimi Desteği Ekleme
-- Tek tek çalıştırmak daha güvenli
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS asset_type ENUM('TL', 'USD', 'EUR', 'GOLD', 'CREDIT_CARD') NOT NULL DEFAULT 'TL' AFTER amount;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS exchange_rate DECIMAL(15,4) DEFAULT 1.0000 AFTER asset_type;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS asset_amount DECIMAL(15,4) AFTER exchange_rate;

-- Mevcut bakiyeleri aktar
INSERT IGNORE INTO inv_entity_balances (entity_id, asset_type, amount)
SELECT id, 'TL', balance FROM inv_entities;
