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

            $heroSlides = $this->fetchHeroSlides($pdo);

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
        } catch (PDOException $exception) {
            // Fall back to static configuration when the database is unavailable.
        }

        return $this->fallback();
    }

    public function update(array $payload): void
    {
        $settings = $this->normalisePayload($payload);

        $pdo = Connection::get();
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
        $sortOrder = (int) $pdo->query('SELECT IFNULL(MAX(sort_order), 0) FROM hero_slides')->fetchColumn();

        $statement = $pdo->prepare('INSERT INTO hero_slides (image_url, label, sort_order) VALUES (:image_url, :label, :sort_order)');
        $statement->execute([
            ':image_url' => trim($imageUrl),
            ':label' => $label !== null && $label !== '' ? $label : null,
            ':sort_order' => $sortOrder + 1,
        ]);
    }

    public function deleteHeroSlide(int $id): ?string
    {
        $pdo = Connection::get();

        $statement = $pdo->prepare('SELECT image_url FROM hero_slides WHERE id = :id');
        $statement->execute([':id' => $id]);
        $imagePath = $statement->fetchColumn();

        $delete = $pdo->prepare('DELETE FROM hero_slides WHERE id = :id');
        $delete->execute([':id' => $id]);

        return is_string($imagePath) ? $imagePath : null;
    }

    private function fetchHeroSlides(PDO $pdo): array
    {
        $statement = $pdo->query('SELECT id, image_url, label FROM hero_slides ORDER BY sort_order ASC, id ASC');
        $slides = $statement->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return array_values(array_map(
            static fn (array $slide): array => [
                'id' => isset($slide['id']) ? (int) $slide['id'] : null,
                'image' => $slide['image_url'] ?? '',
                'label' => $slide['label'] ?? null,
            ],
            $slides
        ));
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
                ],
                [
                    'id' => null,
                    'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1600&q=80',
                    'label' => 'Laguna El Oconal',
                ],
                [
                    'id' => null,
                    'image' => 'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&w=1600&q=80',
                    'label' => 'Cascadas de Pozuzo',
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

    private function nullableTrim(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
