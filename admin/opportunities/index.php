<?php
session_start();
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../auth.php';
require_once __DIR__ . '/../../config/database.php';
function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
$opportunities = $pdo->query("SELECT * FROM opportunities ORDER BY created_at DESC")->fetchAll();
$labels = ['emploi' => 'Offre d’emploi', 'financement' => 'Faire un don'];
$messages = [
    'created' => 'L’opportunité a été publiée.',
    'deleted' => 'L’opportunité a été supprimée.',
    'status' => 'Le statut de l’opportunité a été modifié.',
];
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Opportunit&eacute;s | ORPAIS</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet"><style>body{background:#f7fbfb;color:#163b4d}.page{max-width:1200px;margin:40px auto;padding:0 20px}.header{background:#164e68;color:#fff;border-radius:16px;padding:25px;margin-bottom:20px}.card{border:0;border-radius:16px;box-shadow:0 8px 24px #164e6814}.badge-emploi{background:#dff3f5;color:#164e68}.badge-financement{background:#fde5d8;color:#9b4f32}.badge-visible{background:#dff5e8;color:#207245}.badge-cache{background:#fff0d8;color:#9a6314}.btn-primary{background:#bd6846;border-color:#bd6846}.opportunity-actions{display:flex;flex-wrap:wrap;gap:6px}.status-cell{min-width:110px}</style>    <link href="../../assets/css/admin-common.css" rel="stylesheet">
</head>
<body><main class="page">
<div class="header d-flex justify-content-between align-items-center gap-3"><div><h1>Gestion des opportunités</h1><p class="mb-0">Publiez, masquez ou supprimez vos offres d’emploi et vos campagnes « Faire un don ».</p></div><a href="create.php" class="btn btn-light">+ Nouvelle opportunité</a></div>
<?php if (isset($_GET['success'], $messages[$_GET['success']])): ?><div class="alert alert-success"><?= e($messages[$_GET['success']]) ?></div><?php endif; ?>
<div class="card table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Type</th><th>Titre</th><th>Organisation</th><th>Échéance</th><th>Visibilité</th><th>Actions</th></tr></thead><tbody>
<?php if (!$opportunities): ?><tr><td colspan="6" class="text-center py-5">Aucune opportunité publiée.</td></tr><?php endif; ?>
<?php foreach ($opportunities as $opportunity): ?><tr><td><span class="badge <?= $opportunity['type'] === 'emploi' ? 'badge-emploi' : 'badge-financement' ?>"><?= e($labels[$opportunity['type']] ?? $opportunity['type']) ?></span></td><td><?= e($opportunity['title']) ?></td><td><?= e($opportunity['organization']) ?></td><td><?= $opportunity['deadline'] ? e(date('d/m/Y', strtotime($opportunity['deadline']))) : '—' ?></td><td class="status-cell"><?php if ($opportunity['status'] === 'active'): ?><span class="badge badge-visible"><i class="bi bi-eye"></i> Visible</span><?php else: ?><span class="badge badge-cache"><i class="bi bi-eye-slash"></i> Cachée</span><?php endif; ?></td><td><div class="opportunity-actions"><a class="btn btn-sm <?= $opportunity['status'] === 'active' ? 'btn-outline-warning' : 'btn-outline-success' ?>" href="toggle.php?id=<?= (int)$opportunity['id'] ?>"><?= $opportunity['status'] === 'active' ? '<i class="bi bi-eye-slash"></i> Cacher' : '<i class="bi bi-eye"></i> Rendre visible' ?></a><a class="btn btn-sm btn-outline-danger" href="delete.php?id=<?= (int)$opportunity['id'] ?>" onclick="return confirm('Supprimer définitivement cette opportunité ?')"><i class="bi bi-trash"></i> Supprimer</a></div></td></tr><?php endforeach; ?>
</tbody></table></div></main></body></html>
