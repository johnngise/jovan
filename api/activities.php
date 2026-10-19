<?php

require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, max-age=0');

$scheme = (
    !empty($_SERVER['HTTPS']) &&
    $_SERVER['HTTPS'] !== 'off'
) ? 'https' : 'http';
$projectPath = dirname(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/ORPAICS/api/activities.php')));
$siteBase = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim($projectPath, '/') . '/';

try {
    $statement = $pdo->prepare("
        SELECT id, title, category, category_label, description, icon, image
        FROM activities
        WHERE status = 'active'
        ORDER BY display_order ASC, id ASC
    ");
    $statement->execute();

    $activities = array_map(static function (array $activity) use ($siteBase): array {
        $image = trim((string) ($activity['image'] ?? ''));
        if ($image === '') {
            $image = 'assets/img/orpais.jpeg';
        } elseif (!preg_match('~^(https?:)?//~i', $image) && $image[0] !== '/') {
            $image = $siteBase . ltrim($image, '/');
        }

        return [
            'id' => (int) $activity['id'],
            'title' => (string) $activity['title'],
            'category' => (string) $activity['category'],
            'categoryLabel' => (string) $activity['category_label'],
            'description' => (string) $activity['description'],
            'icon' => (string) ($activity['icon'] ?: 'bi bi-activity'),
            'image' => $image,
            'detailUrl' => $siteBase . 'activity-single.php?id=' . (int) $activity['id'],
        ];
    }, $statement->fetchAll(PDO::FETCH_ASSOC));

    echo json_encode(
        ['siteBase' => $siteBase, 'activities' => $activities],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
    );
} catch (Throwable $exception) {
    error_log('Activities API error: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode(
        ['error' => 'Impossible de charger les activités pour le moment.'],
        JSON_UNESCAPED_UNICODE
    );
}
