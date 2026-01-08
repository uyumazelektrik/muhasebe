-- Faz 8: Karmaşık Finansman ve 3. Taraf Kart Kullanımı Takibi

-- 1. Finansal Araçlar (Cüzdanlar/Kasalar/Krediler) Tablosu
CREATE TABLE IF NOT EXISTS inv_wallets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    owner_entity_id INT NOT NULL COMMENT 'Cüzdanın sahibi (İşletme için özel bir cari veya Personel/Arkadaş)',
    name VARCHAR(100) NOT NULL COMMENT 'Cüzdan Adı (Örn: İş Bankası Kredi Kartı, Ahmetin Kartı)',
    wallet_type ENUM('CASH', 'CREDIT_CARD', 'BANK_ACCOUNT', 'GOLD_ACCOUNT', 'LOAN') NOT NULL DEFAULT 'CASH',
    asset_type ENUM('TL', 'USD', 'EUR', 'GOLD') NOT NULL DEFAULT 'TL',
    `limit_amount` DECIMAL(15,4) DEFAULT 0.0000 COMMENT 'Kredi kartı limiti veya Kredi tutarı',
    `balance` DECIMAL(15,4) DEFAULT 0.0000 COMMENT 'Kasadaki para veya Kartın borcu',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_entity_id) REFERENCES inv_entities(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 2. İşlem Tablosuna Analiz ve Takip Alanları Ekleme
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS wallet_id INT AFTER entity_id;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS installment_count INT DEFAULT 1;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS installment_no INT DEFAULT 1;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS parent_transaction_id INT;
ALTER TABLE inv_entity_transactions ADD COLUMN IF NOT EXISTS commission_fee DECIMAL(15,4) DEFAULT 0.0000 COMMENT 'Banka veya dost komisyonu';

-- 3. İlişkiler
ALTER TABLE inv_entity_transactions ADD CONSTRAINT fk_trans_wallet FOREIGN KEY (wallet_id) REFERENCES inv_wallets(id) ON DELETE SET NULL;
