-- Menambahkan kolom is_taken untuk menandai status pengambilan PO
ALTER TABLE purchase_orders 
ADD COLUMN is_taken TINYINT(1) DEFAULT 0 
COMMENT 'Status pengambilan PO: 0=Belum diambil, 1=Sudah diambil' 
AFTER status;
