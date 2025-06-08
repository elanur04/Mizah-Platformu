CREATE TABLE IF NOT EXISTS takipciler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    takip_eden_id INT NOT NULL,
    takip_edilen_id INT NOT NULL,
    takip_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (takip_eden_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    FOREIGN KEY (takip_edilen_id) REFERENCES kullanicilar(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (takip_eden_id, takip_edilen_id)
); 