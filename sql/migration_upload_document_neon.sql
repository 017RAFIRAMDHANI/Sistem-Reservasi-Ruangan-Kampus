-- Jalankan file ini di Neon SQL Editor jika tabel reservations sudah terlanjur dibuat.
-- Tujuannya agar dokumen pendukung tersimpan di database, bukan folder uploads.

ALTER TABLE reservations
    ADD COLUMN IF NOT EXISTS document_original_name VARCHAR(255) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS document_mime_type VARCHAR(120) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS document_size INTEGER DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS document_data BYTEA DEFAULT NULL;
