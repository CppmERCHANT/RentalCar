<?php
session_start();
require __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT r.*, v.make, v.model, v.year, v.color, v.image_path
    FROM reservations r
    JOIN vehicles v ON r.vehicle_id = v.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$user_id]);
$reservations = $stmt->fetchAll();

function getStatusBadgeClass($status) {
    switch(strtolower($status)) {
        case 'pending':
        case 'en attente':
            return 'bg-warning';
        case 'confirmé':
        case 'confirmed':
            return 'bg-success';
        case 'rejeté':
        case 'rejected':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
function getStatusText($status) {
    switch(strtolower($status)) {
        case 'pending':
            return 'En attente';
        case 'confirmed':
            return 'Confirmé';
        case 'rejected':
            return 'Rejeté';
        default:
            return $status;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Réservations | IdrisLocation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/styledash.css">
    <link rel="stylesheet" href="../../assets/stylemine.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-logo">
                    <i class="bi bi-car-front"></i> IdrisLocation
                </div>
                <div class="sidebar-nav">
                    <a href="../../dashboard.php" class="sidebar-link">
                        <i class="bi bi-house-door"></i> Accueil
                    </a>
                    <a href="../../views/vehicle/index.php" class="sidebar-link">
                        <i class="bi bi-calendar-plus"></i> Faire une réservation
                    </a>
                    <a href="../../views/vehicle/mine.php" class="sidebar-link active">
                        <i class="bi bi-list-check"></i> Mes Réservations
                    </a>
                    <a href="../../logout.php" class="sidebar-link text-danger mt-5">
                        <i class="bi bi-box-arrow-right"></i> Se Déconnecter
                    </a>
                </div>
            </div>
            
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container py-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h2 mb-0">
                            <i class="bi bi-list-check text-primary me-2"></i>
                            Mes Réservations
                        </h1>
                        <a href="../../views/vehicle/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-plus-circle me-2"></i>Nouvelle réservation
                        </a>
                    </div>

                    <?php if (count($reservations) === 0): ?>
                        <div class="empty-state">
                            <i class="bi bi-calendar-x"></i>
                            <h3>Aucune réservation trouvée</h3>
                            <p class="text-muted">Vous n'avez pas encore effectué de réservation de véhicule.</p>
                            <a href="../../views/vehicle/index.php" class="btn btn-primary mt-3">
                                <i class="bi bi-calendar-plus me-2"></i>Réserver un véhicule
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="table-view">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle shadow-sm bg-white mb-4">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Véhicule</th>
                                            <th>Période</th>
                                            <th>Prix total</th>
                                            <th>Statut</th>
                                            <th>Réservé le</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reservations as $res): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php if (!empty($res['image_path'])): ?>
                                                            <img src="<?= htmlspecialchars($res['image_path']) ?>" class="vehicle-image me-3" alt="<?= htmlspecialchars("{$res['make']} {$res['model']}") ?>">
                                                        <?php else: ?>
                                                            <div class="bg-light vehicle-image me-3 d-flex align-items-center justify-content-center">
                                                                <i class="bi bi-car-front text-muted"></i>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <strong><?= htmlspecialchars("{$res['make']} {$res['model']}") ?></strong>
                                                            <div class="text-muted small"><?= htmlspecialchars($res['year']) ?> · <?= htmlspecialchars($res['color']) ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="bi bi-calendar3 text-primary me-1"></i> 
                                                        <?= date('d/m/Y', strtotime($res['start_date'])) ?> 
                                                        <i class="bi bi-arrow-right mx-1"></i> 
                                                        <?= date('d/m/Y', strtotime($res['end_date'])) ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?php
                                                            $start = new DateTime($res['start_date']);
                                                            $end = new DateTime($res['end_date']);
                                                            $days = $start->diff($end)->days;
                                                            echo $days . ' jour' . ($days > 1 ? 's' : '');
                                                        ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong class="text-primary"><?= htmlspecialchars($res['total_price']) ?> €</strong>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge <?= getStatusBadgeClass($res['status']) ?>">
                                                        <?= getStatusText($res['status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div>
                                                        <i class="bi bi-clock text-muted me-1"></i>
                                                        <?= htmlspecialchars(date('d/m/Y', strtotime($res['created_at']))) ?>
                                                    </div>
                                                    <div class="text-muted small">
                                                        <?= htmlspecialchars(date('H:i', strtotime($res['created_at']))) ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>