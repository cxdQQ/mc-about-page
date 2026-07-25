<?php
require_once __DIR__ . '/db.php';

function getTextures(): array {
    $db = getDb();
    
    $stmt = $db->query('SELECT * FROM texture_packs ORDER BY created_at DESC');
    $textures = $stmt->fetchAll();
    
    foreach ($textures as &$t) {
        $t['features'] = json_decode($t['features'] ?? '[]', true) ?: [];
        $t['images'] = json_decode($t['images'] ?? '[]', true) ?: [];
        $t['videos'] = json_decode($t['videos'] ?? '[]', true) ?: [];
        $t['tags'] = json_decode($t['tags'] ?? '[]', true) ?: [];
    }
    
    return $textures;
}

function getTexture(int $id): ?array {
    $db = getDb();
    
    $stmt = $db->prepare('SELECT * FROM texture_packs WHERE id = ?');
    $stmt->execute([$id]);
    $texture = $stmt->fetch();
    
    if (!$texture) return null;
    
    $texture['features'] = json_decode($texture['features'] ?? '[]', true) ?: [];
    $texture['images'] = json_decode($texture['images'] ?? '[]', true) ?: [];
    $texture['videos'] = json_decode($texture['videos'] ?? '[]', true) ?: [];
    $texture['tags'] = json_decode($texture['tags'] ?? '[]', true) ?: [];
    
    return $texture;
}

function createTexture(array $data): int {
    $db = getDb();
    
    $stmt = $db->prepare('INSERT INTO texture_packs (name, slug, description, short_description, cover_image, version, author, release_date, buy_url, features, images, videos, tags) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    
    $stmt->execute([
        $data['name'],
        $data['slug'],
        $data['description'] ?? '',
        $data['short_description'] ?? '',
        $data['cover_image'] ?? '',
        $data['version'] ?? '1.21',
        $data['author'] ?? '茉莉柚茶 Team',
        $data['release_date'] ?? date('Y-m-d'),
        $data['buy_url'] ?? '',
        json_encode($data['features'] ?? []),
        json_encode($data['images'] ?? []),
        json_encode($data['videos'] ?? []),
        json_encode($data['tags'] ?? [])
    ]);
    
    return (int)$db->lastInsertId();
}

function updateTexture(int $id, array $data): bool {
    $db = getDb();
    
    $fields = [];
    $values = [];
    
    $allowed = ['name', 'slug', 'description', 'short_description', 'cover_image', 'version', 'author', 'release_date', 'buy_url', 'features', 'images', 'videos', 'tags'];
    
    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $fields[] = "$field = ?";
            if (in_array($field, ['features', 'images', 'videos', 'tags'])) {
                $values[] = json_encode($data[$field]);
            } else {
                $values[] = $data[$field];
            }
        }
    }
    
    if (empty($fields)) return false;
    
    $values[] = $id;
    $sql = 'UPDATE texture_packs SET ' . implode(', ', $fields) . ', updated_at = CURRENT_TIMESTAMP WHERE id = ?';
    $stmt = $db->prepare($sql);
    return $stmt->execute($values);
}

function deleteTexture(int $id): bool {
    $db = getDb();
    
    $stmt = $db->prepare('DELETE FROM texture_packs WHERE id = ?');
    return $stmt->execute([$id]);
}

function slugify(string $text): string {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    if (function_exists('iconv')) {
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    }
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    if (empty($text) || strlen($text) < 2) {
        $text = substr(md5($text ?: $text . time()), 0, 8);
    }
    return $text;
}

function getTextureBySlug(string $slug): ?array {
    $db = getDb();
    $stmt = $db->prepare('SELECT id FROM texture_packs WHERE slug = ?');
    $stmt->execute([$slug]);
    return $stmt->fetch() ?: null;
}

function ensureUniqueSlug(string $slug, ?int $excludeId = null): string {
    $db = getDb();
    $original = $slug;
    $i = 1;
    while (true) {
        if ($excludeId) {
            $stmt = $db->prepare('SELECT id FROM texture_packs WHERE slug = ? AND id != ?');
            $stmt->execute([$slug, $excludeId]);
        } else {
            $stmt = $db->prepare('SELECT id FROM texture_packs WHERE slug = ?');
            $stmt->execute([$slug]);
        }
        if (!$stmt->fetch()) return $slug;
        $slug = $original . '-' . $i;
        $i++;
    }
}

function jsonResponse(mixed $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>