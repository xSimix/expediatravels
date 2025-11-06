<?php

namespace App\Repositories;

use App\Database\Connection;
use PDO;
use PDOException;

class SiteSettingsRepository
{
    public function get(): array
    {
        try {
            $pdo = Connection::get();
            $statement = $pdo->query('SELECT site_title, site_tagline, contact_emails, contact_phones, contact_addresses, contact_locations, social_links FROM site_settings ORDER BY id ASC LIMIT 1');
            $settings = $statement->fetch(PDO::FETCH_ASSOC) ?: [];

            $heroSlides = $this->getHeroSlides();

            if (!empty($settings)) {
                return [
                    'siteTitle' => $settings['site_title'] ?? $this->fallback()['siteTitle'],
                    'siteTagline' => $settings['site_tagline'] ?? null,
                    'heroSlides' => $heroSlides ?: $this->fallback()['heroSlides'],
                    'contact' => [
                        'emails' => $this->parseList($settings['contact_emails'] ?? ''),
                        'phones' => $this->parseList($settings['contact_phones'] ?? ''),
                        'addresses' => $this->parseList($settings['contact_addresses'] ?? ''),
                        'locations' => $this->parseList($settings['contact_locations'] ?? ''),
                        'social' => $this->parseSocialList($settings['social_links'] ?? ''),
                    ],
                ];
            }

            $fallback = $this->fallback();
            if (!empty($heroSlides)) {
                $fallback['heroSlides'] = $heroSlides;
            }

            return $fallback;
        } catch (PDOException $exception) {
            // Fall back to static configuration when the database is unavailable.
        }

        return $this->fallback();
    }

    public function update(array $payload): void
    {
        $settings = $this->normalisePayload($payload);

        $pdo = Connection::get();
        $this->ensureTablesExist($pdo);
        $statement = $pdo->prepare(
            'INSERT INTO site_settings (id, site_title, site_tagline, contact_emails, contact_phones, contact_addresses, contact_locations, social_links)
             VALUES (1, :title, :tagline, :emails, :phones, :addresses, :locations, :social)
             ON DUPLICATE KEY UPDATE
                site_title = VALUES(site_title),
                site_tagline = VALUES(site_tagline),
                contact_emails = VALUES(contact_emails),
                contact_phones = VALUES(contact_phones),
                contact_addresses = VALUES(contact_addresses),
                contact_locations = VALUES(contact_locations),
                social_links = VALUES(social_links)'
        );

        $statement->execute([
            ':title' => $settings['siteTitle'],
            ':tagline' => $settings['siteTagline'],
            ':emails' => $settings['contactEmails'],
            ':phones' => $settings['contactPhones'],
            ':addresses' => $settings['contactAddresses'],
            ':locations' => $settings['contactLocations'],
            ':social' => $settings['socialLinks'],
        ]);
    }

    public function addHeroSlide(string $imageUrl, ?string $label = null): void
    {
        $pdo = Connection::get();
        $this->ensureTablesExist($pdo);
        $sortOrder = (int) $pdo->query('SELECT IFNULL(MAX(sort_order), 0) FROM hero_slides')->fetchColumn();

        $statement = $pdo->prepare('INSERT INTO hero_slides (image_url, label, sort_order) VALUES (:image_url, :label, :sort_order)');
        $statement->execute([
            ':image_url' => $this->resolveHeroImageUrl($imageUrl),
            ':label' => $label !== null && $label !== '' ? $label : null,
            ':sort_order' => $sortOrder + 1,
        ]);
    }

    public function updateHeroSlide(int $id, array $data): void
    {
        $pdo = Connection::get();
        $this->ensureTablesExist($pdo);

        $fields = [
            'label' => $this->nullableTrim($data['label'] ?? null),
            'alt_text' => $this->nullableTrim($data['alt_text'] ?? null),
            'description' => $this->nullableTrim($data['description'] ?? null),
        ];

        $statement = $pdo->prepare(
            'UPDATE hero_slides SET label = :label, alt_text = :alt_text, description = :description WHERE id = :id'
        );

        $statement->execute([
            ':label' => $fields['label'],
            ':alt_text' => $fields['alt_text'],
            ':description' => $fields['description'],
            ':id' => $id,
        ]);
    }

    public function deleteHeroSlide(int $id): ?string
    {
        $pdo = Connection::get();
        $this->ensureTablesExist($pdo);

        $statement = $pdo->prepare('SELECT image_url FROM hero_slides WHERE id = :id');
        $statement->execute([':id' => $id]);
        $imagePath = $statement->fetchColumn();

        $delete = $pdo->prepare('DELETE FROM hero_slides WHERE id = :id');
        $delete->execute([':id' => $id]);

        return is_string($imagePath) ? $imagePath : null;
    }

    public function getHeroSlides(bool $onlyVisible = true): array
    {
        try {
            $pdo = Connection::get();
            $this->ensureTablesExist($pdo);

            return $this->fetchHeroSlides($pdo, $onlyVisible);
        } catch (PDOException $exception) {
            return [];
        }
    }

    public function updateHeroVisibility(array $visibleIds): void
    {
        $pdo = Connection::get();
        $this->ensureTablesExist($pdo);

        $visibleIds = array_values(array_filter(array_map(static fn ($id) => is_numeric($id) ? (int) $id : null, $visibleIds), static fn ($id) => $id !== null));

        $pdo->beginTransaction();

        try {
            $pdo->exec('UPDATE hero_slides SET is_visible = 0');

            if (!empty($visibleIds)) {
                $placeholders = implode(',', array_fill(0, count($visibleIds), '?'));
                $statement = $pdo->prepare("UPDATE hero_slides SET is_visible = 1 WHERE id IN ($placeholders)");
                $statement->execute($visibleIds);
            }

            $pdo->commit();
        } catch (PDOException $exception) {
            $pdo->rollBack();
            throw $exception;
        }
    }

    private function fetchHeroSlides(PDO $pdo, bool $onlyVisible = true): array
    {
        $query = 'SELECT id, image_url, label, alt_text, description, is_visible FROM hero_slides';

        if ($onlyVisible) {
            $query .= ' WHERE is_visible = 1';
        }

        $query .= ' ORDER BY sort_order ASC, id ASC';

        $statement = $pdo->query($query);
        $slides = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_values(array_map(
            function (array $slide): array {
                $imageUrl = $slide['image_url'] ?? '';

                return [
                    'id' => isset($slide['id']) ? (int) $slide['id'] : null,
                    'image' => $this->resolveHeroImageUrl($imageUrl),
                    'label' => $slide['label'] ?? null,
                    'altText' => $slide['alt_text'] ?? null,
                    'description' => $slide['description'] ?? null,
                    'isVisible' => isset($slide['is_visible']) ? (bool) (int) $slide['is_visible'] : true,
                ];
            },
            $slides
        ));
    }

    private function resolveHeroImageUrl(?string $path): string
    {
        if ($path === null) {
            return '';
        }

        $trimmed = trim((string) $path);

        if ($trimmed === '') {
            return '';
        }

        if (preg_match('~^(?:https?:)?//~i', $trimmed) === 1 || str_starts_with($trimmed, 'data:')) {
            return $trimmed;
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $trimmed), '/');

        return $normalized;
    }

    private function normalisePayload(array $payload): array
    {
        return [
            'siteTitle' => trim((string) ($payload['siteTitle'] ?? 'Expediatravels')) ?: 'Expediatravels',
            'siteTagline' => $this->nullableTrim($payload['siteTagline'] ?? null),
            'contactEmails' => $this->implodeList($payload['contactEmails'] ?? []),
            'contactPhones' => $this->implodeList($payload['contactPhones'] ?? []),
            'contactAddresses' => $this->implodeList($payload['contactAddresses'] ?? []),
            'contactLocations' => $this->implodeList($payload['contactLocations'] ?? []),
            'socialLinks' => $this->implodeList($payload['socialLinks'] ?? []),
        ];
    }

    private function fallback(): array
    {
        return [
            'siteTitle' => 'Expediatravels',
            'siteTagline' => 'Explora la Selva Central',
            'heroSlides' => [
                [
                    'id' => null,
                    'image' => 'https://images.unsplash.com/photo-1529923188384-5e545b81d48d?auto=format&fit=crop&w=1600&q=80',
                    'label' => 'Bosques de Oxapampa',
                    'altText' => 'Bosque de neblina en Oxapampa con luz dorada al amanecer',
                    'description' => 'Paisaje representativo de la Reserva de Biosfera Oxapampa-Ashaninka-Yanesha al amanecer.',
                    'isVisible' => true,
                ],
                [
                    'id' => null,
                    'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
                    'label' => 'Laguna El Oconal',
                    'altText' => 'Reflejos en la Laguna El Oconal en Villa Rica',
                    'description' => 'La laguna El Oconal de Villa Rica al atardecer, ideal para avistamiento de aves.',
                    'isVisible' => true,
                ],
                [
                    'id' => null,
                    'image' => 'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1600&q=80',
                    'label' => 'Cascadas de Pozuzo',
                    'altText' => 'Cascada rodeada de selva en Pozuzo, Selva Central del Perú',
                    'description' => 'Cascadas cristalinas de Pozuzo rodeadas de vegetación amazónica.',
                    'isVisible' => true,
                ],
            ],
            'contact' => [
                'emails' => ['hola@expediatravels.pe'],
                'phones' => ['+51 984 635 885'],
                'addresses' => ['Jr. San Martín 245, Oxapampa'],
                'locations' => ['Oxapampa, Pasco — Perú'],
                'social' => [
                    ['label' => 'Instagram', 'url' => 'https://instagram.com/expediatravels'],
                    ['label' => 'Facebook', 'url' => 'https://facebook.com/expediatravels'],
                ],
            ],
        ];
    }

    private function parseList(?string $value): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $items = preg_split('/\r\n|\r|\n/', (string) $value) ?: [];

        return array_values(array_filter(array_map('trim', $items), static fn ($item) => $item !== ''));
    }

    private function implodeList(array $items): string
    {
        $lines = array_values(array_filter(array_map('trim', $items), static fn ($item) => $item !== ''));

        return implode("\n", $lines);
    }

    private function parseSocialList(?string $value): array
    {
        $lines = $this->parseList($value);
        $social = [];

        foreach ($lines as $line) {
            if (strpos($line, '|') !== false) {
                [$label, $url] = array_map('trim', explode('|', $line, 2));
            } elseif (strpos($line, ',') !== false) {
                [$label, $url] = array_map('trim', explode(',', $line, 2));
            } else {
                $label = $line;
                $url = $line;
            }

            if ($label === '' || $url === '') {
                continue;
            }

            $social[] = [
                'label' => $label,
                'url' => $url,
            ];
        }

        return $social;
    }

    private function ensureTablesExist(PDO $pdo): void
    {
        static $initialised = false;

        if ($initialised) {
            return;
        }

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS site_settings (
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
            )'
        );

        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS hero_slides (
                id INT AUTO_INCREMENT PRIMARY KEY,
                image_url VARCHAR(255) NOT NULL,
                label VARCHAR(120) DEFAULT NULL,
                alt_text VARCHAR(160) DEFAULT NULL,
                description TEXT DEFAULT NULL,
                sort_order INT DEFAULT 0,
                is_visible TINYINT(1) NOT NULL DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )'
        );

        foreach ([
            'ALTER TABLE hero_slides ADD COLUMN is_visible TINYINT(1) NOT NULL DEFAULT 1',
            'ALTER TABLE hero_slides ADD COLUMN alt_text VARCHAR(160) DEFAULT NULL',
            'ALTER TABLE hero_slides ADD COLUMN description TEXT DEFAULT NULL',
        ] as $migration) {
            try {
                $pdo->exec($migration);
            } catch (PDOException $exception) {
                // Column already exists.
            }
        }

        $initialised = true;
    }

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
