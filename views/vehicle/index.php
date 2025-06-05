<?php
session_start();
require '../../config/db.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['vehicle_id'], $_POST['start_date'], $_POST['end_date']) && !isset($_POST['submit_comment'])) {
    $userId = $_SESSION['user']['id'];
    $vehicleId = $_POST['vehicle_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];

    $stmt = $pdo->prepare("SELECT daily_price FROM vehicles WHERE id = ?");
    $stmt->execute([$vehicleId]);
    $vehicle = $stmt->fetch();

    if ($vehicle) {
        $dailyPrice = $vehicle['daily_price'];
        $start = new DateTime($startDate);
        $end = new DateTime($endDate);
        $diff = $start->diff($end);
        $days = $diff->days;

        $totalPrice = $dailyPrice * $days;

        $stmt = $pdo->prepare("INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $vehicleId, $startDate, $endDate, $totalPrice, 'pending']);

        $stmt = $pdo->prepare("UPDATE vehicles SET available = 0 WHERE id = ?");
        $stmt->execute([$vehicleId]);

        $_SESSION['success_message'] = "Votre réservation a été confirmée !";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Véhicule introuvable.";
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_comment'])) {
    $userId = $_SESSION['user']['id'];
    $vehicleId = $_POST['vehicle_id_comment'];
    $content = trim($_POST['content']);
    $rating = intval($_POST['rating']);

    if (!empty($content) && $rating >= 1 && $rating <= 5) {
        $stmt = $pdo->prepare("INSERT INTO comments (user_id, vehicle_id, content, rating, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $vehicleId, $content, $rating]);
        $_SESSION['success_message'] = "Merci pour votre commentaire !";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['error_message'] = "Veuillez fournir un commentaire valide et une note entre 1 et 5.";
    }
}

try {
    $stmt = $pdo->query("SELECT * FROM vehicles WHERE available = 1");
    $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faire une réservation | IdrisLocation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/styledash.css">
    <link rel="stylesheet" href="assets\stylevehi.css">
<style>
        .vehicle-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            height: 100%;
        }
        
        .vehicle-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .vehicle-image {
            height: 180px;
            object-fit: cover;
            border-top-left-radius: calc(0.375rem - 1px);
            border-top-right-radius: calc(0.375rem - 1px);
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            transition: opacity 0.3s ease;
            overflow-y: auto;
        }

        .modal.show {
            display: block;
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            background: white;
            padding: 20px;
            position: relative;
            margin: 5% auto;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 15px;
        }

        .info-section {
            flex: 2;
            min-width: 250px;
        }

        .image-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .image-section img {
            width: 100%;
            max-width: 300px;
            border-radius: 8px;
            object-fit: cover;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .reservation-modal {
            display: none;
            position: fixed;
            z-index: 1100;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }

        .reservation-modal.show {
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease forwards;
        }

        .reservation-content {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            animation: slideUp 0.3s ease;
            position: relative;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 22px;
            cursor: pointer;
            color: #666;
            transition: color 0.2s ease;
        }

        .close-btn:hover {
            color: black;
        }

        .comment-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            max-height: 300px;
            overflow-y: auto;
        }

        .comment {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .rating {
            color: #FFD700;
        }
        
        .alert-fixed {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            z-index: 9999;
        }
</style>
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
                    <a href="index.php" class="sidebar-link active">
                        <i class="bi bi-calendar-plus"></i> Faire une réservation
                    </a>
                    <a href="mine.php" class="sidebar-link">
                        <i class="bi bi-list-check"></i> Mes Réservations
                    </a>
                    <a href="../../logout.php" class="sidebar-link text-danger mt-5">
                        <i class="bi bi-box-arrow-right"></i> Se Déconnecter
                    </a>
                </div>
            </div>
            
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container py-4">

                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show alert-fixed" role="alert">
                            <?= $_SESSION['success_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show alert-fixed" role="alert">
                            <?= $_SESSION['error_message'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error_message']); ?>
                    <?php endif; ?>


                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1 class="h2 mb-0">
                            <i class="bi bi-car-front text-primary me-2"></i>
                            Véhicules disponibles
                        </h1>
                    </div>

                    <?php if (!empty($vehicles)): ?>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <div class="col">
                                    <div class="card vehicle-card h-100" onclick="openModal(<?= $vehicle['id'] ?>)">
                                        <?php if (!empty($vehicle['image_path'])): ?>
                                            <img src="<?= htmlspecialchars($vehicle['image_path']) ?>" class="vehicle-image" alt="<?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?>">
                                        <?php else: ?>
                                            <div class="bg-light vehicle-image d-flex align-items-center justify-content-center">
                                                <i class="bi bi-car-front" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?= htmlspecialchars($vehicle['make']) . ' ' . htmlspecialchars($vehicle['model']) ?></h5>
                                            <p class="card-text text-muted"><?= htmlspecialchars($vehicle['year']) ?> · <?= htmlspecialchars($vehicle['color']) ?></p>
                                            <p class="card-text"><small><?= htmlspecialchars(substr($vehicle['description'], 0, 60)) ?>...</small></p>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-primary"><?= htmlspecialchars($vehicle['daily_price']) ?>€ / jour</span>
                                                <button class="btn btn-sm btn-outline-primary">Détails</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="modal-<?= $vehicle['id'] ?>" class="modal" onclick="closeModal(<?= $vehicle['id'] ?>)">
                                    <div class="modal-content" onclick="event.stopPropagation()">
                                        <span class="close-btn" onclick="closeModal(<?= $vehicle['id'] ?>)">&times;</span>

                                        <div class="modal-row">
                                            <div class="info-section">
                                                <h2><?= htmlspecialchars($vehicle['make']) . ' ' . htmlspecialchars($vehicle['model']) ?></h2>
                                                <div class="row mt-3">
                                                    <div class="col-md-6">
                                                        <p><strong><i class="bi bi-calendar-event text-primary"></i> Année:</strong> <?= htmlspecialchars($vehicle['year']) ?></p>
                                                        <p><strong><i class="bi bi-palette text-primary"></i> Couleur:</strong> <?= htmlspecialchars($vehicle['color']) ?></p>
                                                        <p><strong><i class="bi bi-currency-euro text-primary"></i> Prix/jour:</strong> <?= htmlspecialchars($vehicle['daily_price']) ?>€</p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <p><strong><i class="bi bi-speedometer text-primary"></i> Poids:</strong> <?= htmlspecialchars($vehicle['weight']) ?> kg</p>
                                                    </div>
                                                </div>
                                                <div class="mt-3">
                                                    <h5><i class="bi bi-info-circle text-primary"></i> Description</h5>
                                                    <p><?= htmlspecialchars($vehicle['description']) ?></p>
                                                </div>
                                            </div>
                                            <div class="image-section">
                                                <?php if (!empty($vehicle['image_path'])): ?>
                                                    <img src="<?= htmlspecialchars($vehicle['image_path']) ?>" alt="<?= htmlspecialchars($vehicle['make'] . ' ' . $vehicle['model']) ?>">
                                                <?php else: ?>
                                                    <div class="bg-light d-flex align-items-center justify-content-center" style="width: 300px; height: 200px; border-radius: 8px;">
                                                        <i class="bi bi-car-front" style="font-size: 5rem;"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="d-grid gap-2 mt-3">
                                            <button onclick="openReservationModal(<?= $vehicle['id'] ?>)" class="btn btn-primary">
                                                <i class="bi bi-calendar-check me-2"></i>Réserver ce véhicule
                                            </button>
                                        </div>

                                        <div id="reservation-form-<?= $vehicle['id'] ?>" class="reservation-modal">
                                            <div class="reservation-content">
                                                <span class="close-btn" onclick="closeReservationModal(<?= $vehicle['id'] ?>)">&times;</span>
                                                <h3 class="mb-3">Confirmer la réservation</h3>
                                                <form method="POST" class="needs-validation" novalidate>
                                                    <input type="hidden" name="vehicle_id" value="<?= $vehicle['id'] ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label for="start-<?= $vehicle['id'] ?>" class="form-label">Date de début:</label>
                                                        <input type="date" class="form-control" id="start-<?= $vehicle['id'] ?>" name="start_date" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label for="end-<?= $vehicle['id'] ?>" class="form-label">Date de fin:</label>
                                                        <input type="date" class="form-control" id="end-<?= $vehicle['id'] ?>" name="end_date" required>
                                                    </div>
                                                    
                                                    <div class="alert alert-info mb-3">
                                                        <strong>Total estimé:</strong> <span id="total-<?= $vehicle['id'] ?>">0</span> €
                                                        <input type="hidden" id="input-total-<?= $vehicle['id'] ?>" name="total_price" value="">
                                                    </div>
                                                    
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-success">
                                                            <i class="bi bi-check-circle me-2"></i>Confirmer
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="comment-section mt-4">
                                            <ul class="nav nav-tabs" id="commentTabs-<?= $vehicle['id'] ?>" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" id="comments-tab-<?= $vehicle['id'] ?>" data-bs-toggle="tab" 
                                                        data-bs-target="#comments-<?= $vehicle['id'] ?>" type="button" role="tab" aria-selected="true">
                                                        <i class="bi bi-chat-text me-1"></i>Commentaires
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" id="leave-comment-tab-<?= $vehicle['id'] ?>" data-bs-toggle="tab" 
                                                        data-bs-target="#leave-comment-<?= $vehicle['id'] ?>" type="button" role="tab" aria-selected="false">
                                                        <i class="bi bi-pencil me-1"></i>Laisser un commentaire
                                                    </button>
                                                </li>
                                            </ul>
                                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="commentTabContent-<?= $vehicle['id'] ?>">
                                                <div class="tab-pane fade show active" id="comments-<?= $vehicle['id'] ?>" role="tabpanel">
                                                    <?php
                                                    $stmt = $pdo->prepare("SELECT c.content, c.rating, c.created_at, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.vehicle_id = ? ORDER BY c.created_at DESC");
                                                    $stmt->execute([$vehicle['id']]);
                                                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                                    ?>
                                                    
                                                    <?php if (!empty($comments)): ?>
                                                        <?php foreach ($comments as $comment): ?>
                                                            <div class="comment">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div>
                                                                        <h6 class="mb-0">
                                                                            <i class="bi bi-person-circle me-1"></i>
                                                                            <?= htmlspecialchars($comment['username']) ?>
                                                                        </h6>
                                                                        <p class="text-muted small"><?= date('d/m/Y H:i', strtotime($comment['created_at'])) ?></p>
                                                                    </div>
                                                                    <div class="rating">
                                                                        <?= str_repeat('<i class="bi bi-star-fill"></i>', $comment['rating']) ?>
                                                                        <?= str_repeat('<i class="bi bi-star"></i>', 5 - $comment['rating']) ?>
                                                                        <span class="ms-1">(<?= $comment['rating'] ?>/5)</span>
                                                                    </div>
                                                                </div>
                                                                <p class="mt-2"><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php else: ?>
                                                        <div class="text-center py-3">
                                                            <i class="bi bi-chat-square text-muted" style="font-size: 2rem;"></i>
                                                            <p class="mt-2">Aucun commentaire pour ce véhicule.</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="tab-pane fade" id="leave-comment-<?= $vehicle['id'] ?>" role="tabpanel">
                                                    <form method="POST" class="needs-validation" novalidate>
                                                        <input type="hidden" name="vehicle_id_comment" value="<?= $vehicle['id'] ?>">
                                                        
                                                        <div class="mb-3">
                                                            <label for="rating-<?= $vehicle['id'] ?>" class="form-label">Note (1-5):</label>
                                                            <select class="form-select" id="rating-<?= $vehicle['id'] ?>" name="rating" required>
                                                                <option value="">--Sélectionnez une note--</option>
                                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                                    <option value="<?= $i ?>"><?= $i ?> <?= str_repeat('⭐', $i) ?></option>
                                                                <?php endfor; ?>
                                                            </select>
                                                        </div>
                                                        
                                                        <div class="mb-3">
                                                            <label for="content-<?= $vehicle['id'] ?>" class="form-label">Votre commentaire:</label>
                                                            <textarea class="form-control" id="content-<?= $vehicle['id'] ?>" name="content" rows="4" required></textarea>
                                                        </div>
                                                        
                                                        <div class="d-grid">
                                                            <button type="submit" name="submit_comment" class="btn btn-primary">
                                                                <i class="bi bi-send me-2"></i>Envoyer
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-car-front text-muted" style="font-size: 4rem;"></i>
                            <h3 class="mt-3">Aucun véhicule disponible actuellement.</h3>
                            <p class="text-muted">Veuillez vérifier ultérieurement pour de nouvelles disponibilités.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function openModal(id) {
        document.getElementById('modal-' + id).classList.add('show');
        document.getElementById('modal-' + id).style.display = 'block';
    }
    
    function closeModal(id) {
        document.getElementById('modal-' + id).classList.remove('show');
        document.getElementById('modal-' + id).style.display = 'none';
    }
    
    function openReservationModal(id) {
        document.getElementById('reservation-form-' + id).classList.add('show');
    }
    
    function closeReservationModal(id) {
        document.getElementById('reservation-form-' + id).classList.remove('show');
    }
    
    function calculateTotal(vehicleId, dailyPrice) {
        const start = document.getElementById('start-' + vehicleId).value;
        const end = document.getElementById('end-' + vehicleId).value;

        if (start && end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            const diffTime = endDate - startDate;
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 0) {
                const total = diffDays * dailyPrice;
                document.getElementById('total-' + vehicleId).textContent = total;
                document.getElementById('input-total-' + vehicleId).value = total;
            }
        }
    }

    <?php foreach ($vehicles as $vehicle): ?>
    document.getElementById('start-<?= $vehicle['id'] ?>').addEventListener('change', function() {
        calculateTotal(<?= $vehicle['id'] ?>, <?= $vehicle['daily_price'] ?>);
    });
    document.getElementById('end-<?= $vehicle['id'] ?>').addEventListener('change', function() {
        calculateTotal(<?= $vehicle['id'] ?>, <?= $vehicle['daily_price'] ?>);
    });
    <?php endforeach; ?>
    
    window.addEventListener('DOMContentLoaded', (event) => {
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert-fixed');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
    </script>
</body>
</html>