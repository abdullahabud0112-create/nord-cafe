-- ==============================================================================
-- ERD DATABASE SISTEM PEMESANAN CAFE (NORD CAFE) - SUPABASE / POSTGRESQL SCHEMA
-- ==============================================================================

-- 1. CLEANUP PREVIOUS TABLES IF EXISTS (REVERSE ORDER OF DEPENDENCY)
DROP TABLE IF EXISTS qr_scans CASCADE;
DROP TABLE IF EXISTS order_qr CASCADE;
DROP TABLE IF EXISTS order_status_log CASCADE;
DROP TABLE IF EXISTS payments CASCADE;
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS cashiers CASCADE;
DROP TABLE IF EXISTS product_variants CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS customers CASCADE;

-- ==============================================================================
-- 2. CREATE TABLES
-- ==============================================================================

-- Table: CUSTOMERS
CREATE TABLE customers (
    customer_id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    email VARCHAR(255),
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: CATEGORIES
CREATE TABLE categories (
    category_id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

-- Table: PRODUCTS
CREATE TABLE products (
    product_id BIGSERIAL PRIMARY KEY,
    category_id BIGINT NOT NULL REFERENCES categories(category_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    image_url TEXT,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

-- Table: PRODUCT_VARIANTS
CREATE TABLE product_variants (
    variant_id BIGSERIAL PRIMARY KEY,
    product_id BIGINT NOT NULL REFERENCES products(product_id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    additional_price NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);

-- Table: CASHIERS
CREATE TABLE cashiers (
    cashier_id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    employee_code VARCHAR(50) UNIQUE NOT NULL,
    active BOOLEAN NOT NULL DEFAULT TRUE
);

-- Table: ORDERS
CREATE TABLE orders (
    order_id BIGSERIAL PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    customer_id BIGINT REFERENCES customers(customer_id) ON DELETE SET NULL,
    order_date TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    subtotal NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    discount NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    tax NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    service_fee NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    grand_total NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    status VARCHAR(50) NOT NULL DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Table: ORDER_ITEMS
CREATE TABLE order_items (
    order_item_id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
    product_id BIGINT NOT NULL REFERENCES products(product_id) ON DELETE RESTRICT,
    variant_id BIGINT REFERENCES product_variants(variant_id) ON DELETE SET NULL,
    product_name_snapshot VARCHAR(255) NOT NULL,
    unit_price NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    qty INT NOT NULL DEFAULT 1,
    subtotal NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    item_note TEXT
);

-- Table: PAYMENTS
CREATE TABLE payments (
    payment_id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
    method VARCHAR(50) NOT NULL, -- e.g. 'qris', 'cash', 'debit', 'credit', 'transfer'
    amount NUMERIC(12, 2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending', -- 'pending', 'success', 'failed', 'refunded'
    paid_at TIMESTAMPTZ,
    reference_no VARCHAR(100)
);

-- Table: ORDER_STATUS_LOG
CREATE TABLE order_status_log (
    status_log_id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL,
    changed_by VARCHAR(100) NOT NULL,
    changed_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    note TEXT
);

-- Table: ORDER_QR (1:1 with ORDERS)
CREATE TABLE order_qr (
    qr_id BIGSERIAL PRIMARY KEY,
    order_id BIGINT NOT NULL UNIQUE REFERENCES orders(order_id) ON DELETE CASCADE,
    qr_token VARCHAR(255) UNIQUE NOT NULL,
    qr_payload TEXT NOT NULL,
    generated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expired_at TIMESTAMPTZ,
    scan_count INT NOT NULL DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'active' -- 'active', 'scanned', 'expired'
);

-- Table: QR_SCANS
CREATE TABLE qr_scans (
    scan_id BIGSERIAL PRIMARY KEY,
    qr_id BIGINT NOT NULL REFERENCES order_qr(qr_id) ON DELETE CASCADE,
    order_id BIGINT NOT NULL REFERENCES orders(order_id) ON DELETE CASCADE,
    cashier_id BIGINT REFERENCES cashiers(cashier_id) ON DELETE SET NULL,
    scanned_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    result VARCHAR(50) NOT NULL -- 'valid', 'expired', 'already_used', 'invalid'
);

-- ==============================================================================
-- 3. INDEXES FOR PERFORMANCE
-- ==============================================================================
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_product_variants_product ON product_variants(product_id);
CREATE INDEX idx_orders_customer ON orders(customer_id);
CREATE INDEX idx_orders_order_number ON orders(order_number);
CREATE INDEX idx_orders_status ON orders(status);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_order_items_product ON order_items(product_id);
CREATE INDEX idx_payments_order ON payments(order_id);
CREATE INDEX idx_order_status_log_order ON order_status_log(order_id);
CREATE INDEX idx_order_qr_token ON order_qr(qr_token);
CREATE INDEX idx_order_qr_order ON order_qr(order_id);
CREATE INDEX idx_qr_scans_qr ON qr_scans(qr_id);
CREATE INDEX idx_qr_scans_order ON qr_scans(order_id);

-- ==============================================================================
-- 4. ROW LEVEL SECURITY (RLS) FOR SUPABASE API ACCESS (ANON & AUTHENTICATED)
-- ==============================================================================
ALTER TABLE customers ENABLE ROW LEVEL SECURITY;
ALTER TABLE categories ENABLE ROW LEVEL SECURITY;
ALTER TABLE products ENABLE ROW LEVEL SECURITY;
ALTER TABLE product_variants ENABLE ROW LEVEL SECURITY;
ALTER TABLE cashiers ENABLE ROW LEVEL SECURITY;
ALTER TABLE orders ENABLE ROW LEVEL SECURITY;
ALTER TABLE order_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE order_status_log ENABLE ROW LEVEL SECURITY;
ALTER TABLE order_qr ENABLE ROW LEVEL SECURITY;
ALTER TABLE qr_scans ENABLE ROW LEVEL SECURITY;

-- Allow public read & write access for anon & authenticated roles via Supabase client:
CREATE POLICY "Public Read Access Customers" ON customers FOR SELECT USING (true);
CREATE POLICY "Public Insert Customers" ON customers FOR INSERT WITH CHECK (true);
CREATE POLICY "Public Update Customers" ON customers FOR UPDATE USING (true);

CREATE POLICY "Public Read Access Categories" ON categories FOR SELECT USING (true);
CREATE POLICY "Public Insert/Update Categories" ON categories FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Public Read Access Products" ON products FOR SELECT USING (true);
CREATE POLICY "Public Insert/Update Products" ON products FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Public Read Access Variants" ON product_variants FOR SELECT USING (true);
CREATE POLICY "Public Insert/Update Variants" ON product_variants FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Public Read Access Cashiers" ON cashiers FOR SELECT USING (true);
CREATE POLICY "Public Manage Cashiers" ON cashiers FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Public Read Orders" ON orders FOR SELECT USING (true);
CREATE POLICY "Public Insert Orders" ON orders FOR INSERT WITH CHECK (true);
CREATE POLICY "Public Update Orders" ON orders FOR UPDATE USING (true);

CREATE POLICY "Public Read Order Items" ON order_items FOR SELECT USING (true);
CREATE POLICY "Public Insert Order Items" ON order_items FOR INSERT WITH CHECK (true);

CREATE POLICY "Public Read Payments" ON payments FOR SELECT USING (true);
CREATE POLICY "Public Insert Payments" ON payments FOR INSERT WITH CHECK (true);

CREATE POLICY "Public Read Status Logs" ON order_status_log FOR SELECT USING (true);
CREATE POLICY "Public Insert Status Logs" ON order_status_log FOR INSERT WITH CHECK (true);

CREATE POLICY "Public Read Order QR" ON order_qr FOR SELECT USING (true);
CREATE POLICY "Public Insert/Update Order QR" ON order_qr FOR ALL USING (true) WITH CHECK (true);

CREATE POLICY "Public Read QR Scans" ON qr_scans FOR SELECT USING (true);
CREATE POLICY "Public Insert QR Scans" ON qr_scans FOR INSERT WITH CHECK (true);

-- ==============================================================================
-- 5. SEED DATA (DATA AWAL LENGKAP REALISTIS SESUAI ERD)
-- ==============================================================================

-- Seed CUSTOMERS
INSERT INTO customers (customer_id, name, phone, email, created_at) VALUES
(1, 'Dimas Anggara', '081234567890', 'dimas@example.com', NOW() - INTERVAL '2 days'),
(2, 'Sarah Amanda', '085712345678', 'sarah.amanda@example.com', NOW() - INTERVAL '1 days'),
(3, 'Reza Rahadian', '081987654321', 'reza.r@example.com', NOW() - INTERVAL '12 hours'),
(4, 'Clara Sinta', '082199887766', 'clara@example.com', NOW() - INTERVAL '4 hours'),
(5, 'Guest Customer', NULL, NULL, NOW());

-- Seed CATEGORIES
INSERT INTO categories (category_id, name, description, is_active) VALUES
(1, 'Basic Coffee', 'Kopi pilihan klasik berbasis espresso dengan biji kopi arabika pilihan', true),
(2, 'Signature Coffee', 'Minuman kopi kreasi spesial racikan barista Nord Cafe', true),
(3, 'Non Coffee', 'Minuman lezat tanpa kafein dengan berbagai varian rasa premium', true),
(4, 'Yaqult Series', 'Kombinasi kesegaran yakult dengan sirup buah-buahan tropis', true),
(5, 'Tea Series', 'Teh pilihan yang diseduh segar dengan aroma buah menyegarkan', true),
(6, 'Pastry & Snacks', 'Camilan dan roti lezat pendamping kopi favorit', true);

-- Seed PRODUCTS
INSERT INTO products (product_id, category_id, name, description, price, stock, image_url, is_active) VALUES
-- Basic Coffee
(1, 1, 'Espresso Single', 'Ekstraksi murni biji kopi arabika house blend single shot pekat', 16000, 100, 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=600&q=80', true),
(2, 1, 'Americano', 'Espresso dipadukan dengan air mineral panas atau es dingin menyegarkan', 21000, 150, 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80', true),
(3, 1, 'Caffe Latte', 'Espresso seimbang dengan susu steamed yang lembut dan microfoam creamy', 23000, 120, 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80', true),
(4, 1, 'Cappuccino', 'Paduan seimbang espresso, steamed milk, dan busa tebal bertabur bubuk cokelat', 23000, 80, 'https://images.unsplash.com/photo-1534778101976-62847782c213?auto=format&fit=crop&w=600&q=80', true),

-- Signature Coffee
(5, 2, 'Nord Palm Sugar', 'Kopi susu gula aren signature Nord Cafe dengan racikan krimer rahasia', 23000, 200, 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80', true),
(6, 2, 'Pistachio Latte', 'Espresso lembut berpadu dengan sirup pistachio gurih nutty dan susu segar', 23000, 60, 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=600&q=80', true),
(7, 2, 'Butterscotch Latte', 'Sensasi manis legit saus butterscotch mewah dipadu espresso creamy', 23000, 75, 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=600&q=80', true),

-- Non Coffee
(8, 3, 'Matcha Latte', 'Pure matcha Uji Jepang kualitas ceremonial berpadu susu creamy lembut', 23000, 90, 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80', true),
(9, 3, 'Red Velvet', 'Minuman red velvet lembut gurih manis dengan aroma vanila yang khas', 21000, 70, 'https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80', true),
(10, 3, 'Choco Oreo', 'Cokelat Belgia pekat dengan remukan biskuit Oreo renyah melimpah', 21000, 85, 'https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=600&q=80', true),

-- Yaqult Series
(11, 4, 'Mango Yaqult', 'Kombinasi rasa asam manis Yakult dengan sari mangga segar pilihan', 22000, 50, 'https://images.unsplash.com/photo-1546173159-315724a31696?auto=format&fit=crop&w=600&q=80', true),
(12, 4, 'Lychee Yaqult', 'Minuman asam segar Yakult berpadu aroma harum manis buah leci', 22000, 60, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80', true),

-- Tea Series
(13, 5, 'Lychee Tea', 'Black tea seduh segar dengan sirup leci dan buah leci utuh manis', 20000, 110, 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80', true),
(14, 5, 'Lemon Tea', 'Teh aromatik dipadukan dengan kesegaran perasan buah lemon asli', 20000, 95, 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80', true),

-- Pastry
(15, 6, 'Butter Croissant', 'Pastry khas Perancis berlapis renyah dengan aroma mentega murni yang wangi', 18000, 30, 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=600&q=80', true);

-- Seed PRODUCT_VARIANTS
INSERT INTO product_variants (variant_id, product_id, name, additional_price, is_active) VALUES
-- Variants for Americano (product_id = 2)
(1, 2, 'Hot', 0, true),
(2, 2, 'Iced', 0, true),
(3, 2, 'Upsize Large', 4000, true),

-- Variants for Caffe Latte (product_id = 3)
(4, 3, 'Hot', 0, true),
(5, 3, 'Iced', 0, true),
(6, 3, 'Oat Milk Substitute', 6000, true),
(7, 3, 'Extra Shot Espresso', 5000, true),

-- Variants for Nord Palm Sugar (product_id = 5)
(8, 5, 'Normal Sweetness (100%)', 0, true),
(9, 5, 'Less Sweet (70%)', 0, true),
(10, 5, 'Oat Milk Version', 6000, true),
(11, 5, 'Upsize Large', 5000, true),

-- Variants for Matcha Latte (product_id = 8)
(12, 8, 'Hot', 0, true),
(13, 8, 'Iced', 0, true),
(14, 8, 'Less Sugar', 0, true),
(15, 8, 'Oat Milk Substitute', 6000, true);

-- Seed CASHIERS
INSERT INTO cashiers (cashier_id, name, employee_code, active) VALUES
(1, 'Siti Nurhaliza', 'CSH-001', true),
(2, 'Budi Santoso', 'CSH-002', true),
(3, 'Andi Wijaya', 'CSH-003', true);

-- Seed ORDERS
INSERT INTO orders (order_id, order_number, customer_id, order_date, subtotal, discount, tax, service_fee, grand_total, status, notes, created_at) VALUES
(1, 'ORD-20260917-001', 1, NOW() - INTERVAL '3 hours', 46000, 0, 4600, 2000, 52600, 'completed', 'Duduk di sofa lantai 1', NOW() - INTERVAL '3 hours'),
(2, 'ORD-20260917-002', 2, NOW() - INTERVAL '1 hours', 29000, 2000, 2700, 1000, 30700, 'ready', 'Takeaway bawa pulang', NOW() - INTERVAL '1 hours'),
(3, 'ORD-20260917-003', 3, NOW() - INTERVAL '25 minutes', 51000, 0, 5100, 2000, 58100, 'preparing', 'Gelas terpisah jika memungkinkan', NOW() - INTERVAL '25 minutes'),
(4, 'ORD-20260917-004', 4, NOW() - INTERVAL '10 minutes', 23000, 0, 2300, 1000, 26300, 'paid', 'Dine-in meja No. 5', NOW() - INTERVAL '10 minutes'),
(5, 'ORD-20260917-005', NULL, NOW() - INTERVAL '2 minutes', 21000, 0, 2100, 1000, 24100, 'pending', 'Order mandiri via scan QR meja', NOW() - INTERVAL '2 minutes');

-- Seed ORDER_ITEMS (Snapshots product_name and unit_price)
INSERT INTO order_items (order_item_id, order_id, product_id, variant_id, product_name_snapshot, unit_price, qty, subtotal, item_note) VALUES
-- Order 1 items
(1, 1, 5, 8, 'Nord Palm Sugar (Normal Sweetness)', 23000, 1, 23000, 'Es batu sedang'),
(2, 1, 3, 5, 'Caffe Latte (Iced)', 23000, 1, 23000, 'Gula cair dipisah'),

-- Order 2 items
(3, 2, 8, 15, 'Matcha Latte (Oat Milk Substitute)', 29000, 1, 29000, 'Less ice please'),

-- Order 3 items
(4, 3, 5, 10, 'Nord Palm Sugar (Oat Milk Version)', 29000, 1, 29000, 'Normal sugar'),
(5, 3, 11, NULL, 'Mango Yaqult', 22000, 1, 22000, 'Extra segar'),

-- Order 4 items
(6, 4, 6, NULL, 'Pistachio Latte', 23000, 1, 23000, 'Normal ice'),

-- Order 5 items
(7, 5, 2, 2, 'Americano (Iced)', 21000, 1, 21000, 'No sugar');

-- Seed PAYMENTS
INSERT INTO payments (payment_id, order_id, method, amount, payment_status, paid_at, reference_no) VALUES
(1, 1, 'qris', 52600, 'success', NOW() - INTERVAL '2 hours 55 minutes', 'QRIS-NORD-98234710'),
(2, 2, 'debit', 30700, 'success', NOW() - INTERVAL '58 minutes', 'BCA-DBT-44819201'),
(3, 3, 'cash', 58100, 'success', NOW() - INTERVAL '22 minutes', 'CASH-REC-001092'),
(4, 4, 'qris', 26300, 'success', NOW() - INTERVAL '8 minutes', 'QRIS-NORD-98234855');

-- Seed ORDER_STATUS_LOG (Audit Trail per ERD note)
INSERT INTO order_status_log (status_log_id, order_id, status, changed_by, changed_at, note) VALUES
-- Logs for Order 1
(1, 1, 'pending', 'Customer / System', NOW() - INTERVAL '3 hours', 'Pesanan berhasil dibuat oleh pelanggan'),
(2, 1, 'paid', 'Kasir Siti (CSH-001)', NOW() - INTERVAL '2 hours 55 minutes', 'Pembayaran QRIS diverifikasi'),
(3, 1, 'preparing', 'Barista Kitchen', NOW() - INTERVAL '2 hours 50 minutes', 'Pesanan mulai diracik'),
(4, 1, 'ready', 'Barista Kitchen', NOW() - INTERVAL '2 hours 40 minutes', 'Minuman siap di pick-up counter'),
(5, 1, 'completed', 'Kasir Siti (CSH-001)', NOW() - INTERVAL '2 hours 30 minutes', 'Pesanan telah diambil oleh customer'),

-- Logs for Order 2
(6, 2, 'pending', 'Customer / System', NOW() - INTERVAL '1 hours', 'Pesanan baru masuk'),
(7, 2, 'paid', 'Kasir Budi (CSH-002)', NOW() - INTERVAL '58 minutes', 'Pembayaran debit sukses'),
(8, 2, 'preparing', 'Barista Kitchen', NOW() - INTERVAL '50 minutes', 'Sedang dibuat'),
(9, 2, 'ready', 'Barista Kitchen', NOW() - INTERVAL '10 minutes', 'Pesanan siap diambil'),

-- Logs for Order 3
(10, 3, 'pending', 'Customer / System', NOW() - INTERVAL '25 minutes', 'Pesanan masuk via Self-Order'),
(11, 3, 'paid', 'Kasir Siti (CSH-001)', NOW() - INTERVAL '22 minutes', 'Pembayaran tunai diterima kasir'),
(12, 3, 'preparing', 'Barista Kitchen', NOW() - INTERVAL '20 minutes', 'Barista sedang meracik kopi'),

-- Logs for Order 4
(13, 4, 'pending', 'Customer / System', NOW() - INTERVAL '10 minutes', 'Pesanan baru'),
(14, 4, 'paid', 'Kasir Siti (CSH-001)', NOW() - INTERVAL '8 minutes', 'Pembayaran QRIS sukses'),

-- Logs for Order 5
(15, 5, 'pending', 'Customer / System', NOW() - INTERVAL '2 minutes', 'Menunggu konfirmasi dan pembayaran');

-- Seed ORDER_QR (1:1 with ORDERS, contains order_number + token + signature)
INSERT INTO order_qr (qr_id, order_id, qr_token, qr_payload, generated_at, expired_at, scan_count, status) VALUES
(1, 1, 'tok_nord_a1b2c3d4e5f601', '{"order_number":"ORD-20260917-001","token":"tok_nord_a1b2c3d4e5f601","sig":"sha256_89efc0128a"}', NOW() - INTERVAL '3 hours', NOW() + INTERVAL '21 hours', 2, 'scanned'),
(2, 2, 'tok_nord_b2c3d4e5f6a102', '{"order_number":"ORD-20260917-002","token":"tok_nord_b2c3d4e5f6a102","sig":"sha256_77bd8319aa"}', NOW() - INTERVAL '1 hours', NOW() + INTERVAL '23 hours', 1, 'scanned'),
(3, 3, 'tok_nord_c3d4e5f6a1b203', '{"order_number":"ORD-20260917-003","token":"tok_nord_c3d4e5f6a1b203","sig":"sha256_99fe1023bc"}', NOW() - INTERVAL '25 minutes', NOW() + INTERVAL '23 hours 35 minutes', 1, 'scanned'),
(4, 4, 'tok_nord_d4e5f6a1b2c304', '{"order_number":"ORD-20260917-004","token":"tok_nord_d4e5f6a1b2c304","sig":"sha256_11aa4455dd"}', NOW() - INTERVAL '10 minutes', NOW() + INTERVAL '23 hours 50 minutes', 0, 'active'),
(5, 5, 'tok_nord_e5f6a1b2c3d405', '{"order_number":"ORD-20260917-005","token":"tok_nord_e5f6a1b2c3d405","sig":"sha256_22bb5566ee"}', NOW() - INTERVAL '2 minutes', NOW() + INTERVAL '23 hours 58 minutes', 0, 'active');

-- Seed QR_SCANS
INSERT INTO qr_scans (scan_id, qr_id, order_id, cashier_id, scanned_at, result) VALUES
(1, 1, 1, 1, NOW() - INTERVAL '2 hours 56 minutes', 'valid'),
(2, 1, 1, 1, NOW() - INTERVAL '2 hours 30 minutes', 'valid'),
(3, 2, 2, 2, NOW() - INTERVAL '59 minutes', 'valid'),
(4, 3, 3, 1, NOW() - INTERVAL '23 minutes', 'valid');

-- ==============================================================================
-- 6. RESET SEQUENCE COUNTERS TO MAX ID (SO NEXT INSERTS SUCCEED CLEANLY)
-- ==============================================================================
SELECT setval('customers_customer_id_seq', (SELECT COALESCE(MAX(customer_id), 1) FROM customers));
SELECT setval('categories_category_id_seq', (SELECT COALESCE(MAX(category_id), 1) FROM categories));
SELECT setval('products_product_id_seq', (SELECT COALESCE(MAX(product_id), 1) FROM products));
SELECT setval('product_variants_variant_id_seq', (SELECT COALESCE(MAX(variant_id), 1) FROM product_variants));
SELECT setval('cashiers_cashier_id_seq', (SELECT COALESCE(MAX(cashier_id), 1) FROM cashiers));
SELECT setval('orders_order_id_seq', (SELECT COALESCE(MAX(order_id), 1) FROM orders));
SELECT setval('order_items_order_item_id_seq', (SELECT COALESCE(MAX(order_item_id), 1) FROM order_items));
SELECT setval('payments_payment_id_seq', (SELECT COALESCE(MAX(payment_id), 1) FROM payments));
SELECT setval('order_status_log_status_log_id_seq', (SELECT COALESCE(MAX(status_log_id), 1) FROM order_status_log));
SELECT setval('order_qr_qr_id_seq', (SELECT COALESCE(MAX(qr_id), 1) FROM order_qr));
SELECT setval('qr_scans_scan_id_seq', (SELECT COALESCE(MAX(scan_id), 1) FROM qr_scans));
