-- Ensure configuration tables exist for hero backgrounds and site settings.
CREATE TABLE IF NOT EXISTS site_settings (
    id INT PRIMARY KEY,
    site_title VARCHAR(150) NOT NULL,
    site_tagline VARCHAR(150) DEFAULT NULL,
    contact_emails TEXT DEFAULT NULL,
    contact_phones TEXT DEFAULT NULL,
    contact_addresses TEXT DEFAULT NULL,
    contact_locations TEXT DEFAULT NULL,
    social_links TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hero_slides (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    label VARCHAR(120) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
