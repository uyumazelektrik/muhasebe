-- Fatura/Fiş Yönetimi Tabloları

-- Ana fatura tablosu
CREATE TABLE IF NOT EXISTS inv_invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL UNIQUE,
    entity_id INT NOT NULL,
    type ENUM('purchase', 'sale') NOT NULL COMMENT 'Alış veya Satış',
    invoice_date DATE NOT NULL,
    total_amount DECIMAL(15,4) DEFAULT 0.0000,
    tax_amount DECIMAL(15,4) DEFAULT 0.0000,
    discount_amount DECIMAL(15,4) DEFAULT 0.0000,
    net_amount DECIMAL(15,4) DEFAULT 0.0000,
    notes TEXT,
    status ENUM('draft', 'finalized', 'cancelled') DEFAULT 'finalized',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (entity_id) REFERENCES inv_entities(id) ON DELETE RESTRICT,
    INDEX idx_invoice_no (invoice_no),
    INDEX idx_entity (entity_id),
    INDEX idx_type (type),
    INDEX idx_date (invoice_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Fatura kalemleri (ürünler ve giderler)
CREATE TABLE IF NOT EXISTS inv_invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_type ENUM('stock', 'expense') NOT NULL,
    product_id INT NULL COMMENT 'Stok kalemi ise ürün ID',
    expense_category_id INT NULL COMMENT 'Gider kalemi ise kategori ID',
    description VARCHAR(500) NOT NULL,
    quantity DECIMAL(10,3) DEFAULT 1.000,
    unit VARCHAR(20) DEFAULT 'Adet',
    unit_price DECIMAL(15,4) NOT NULL,
    tax_rate DECIMAL(5,2) DEFAULT 0.00,
    tax_amount DECIMAL(15,4) DEFAULT 0.0000,
    discount_amount DECIMAL(15,4) DEFAULT 0.0000,
    total_amount DECIMAL(15,4) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES inv_invoices(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES inv_products(id) ON DELETE SET NULL,
    INDEX idx_invoice (invoice_id),
    INDEX idx_item_type (item_type),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
