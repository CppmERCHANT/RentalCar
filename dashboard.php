<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil | IdrisLocation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets\styledash.css">

</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="sidebar-logo">
                    <i class="bi bi-car-front"></i> IdrisLocation
                </div>
                <div class="sidebar-nav">
                    <a href="dashboard.php" class="sidebar-link active">
                        <i class="bi bi-house-door"></i> Accueil
                    </a>
                    <a href="views\vehicle\index.php" class="sidebar-link">
                        <i class="bi bi-calendar-plus"></i> Faire une réservation
                    </a>
                    <a href="views\vehicle\mine.php" class="sidebar-link">
                        <i class="bi bi-list-check"></i> Mes Réservations
                    </a>
                    <a href="logout.php" class="sidebar-link text-danger mt-5">
                        <i class="bi bi-box-arrow-right"></i> Se Déconnecter
                    </a>
                </div>
            </div>
            
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container py-4">
                    <div class="welcome-container">
                        <div class="user-welcome">
                            <h2 class="display-6">
                                <i class="bi bi-person-circle text-primary me-2"></i>
                                Bienvenue, <?= htmlspecialchars($_SESSION['user']['username']) ?> !
                            </h2>
                            <p class="text-muted">Connecté en tant que <?= htmlspecialchars($_SESSION['user']['email'] ?? $_SESSION['user']['username']) ?></p>
                        </div>
                        
                        <div class="company-intro">
                            <h3 class="h4 text-primary">IdrisLocation - Votre Solution de Location</h3>
                            <p class="lead">
                                Bienvenue sur notre plateforme de réservation de véhicules. Chez IdrisLocation, nous vous offrons une large gamme de véhicules pour répondre à tous vos besoins de déplacement.
                            </p>
                            <div class="row mt-4">
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <i class="bi bi-star text-warning" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-3">Qualité Premium</h5>
                                            <p class="card-text">Véhicules récents et bien entretenus pour votre sécurité.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <i class="bi bi-currency-euro text-success" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-3">Tarifs Compétitifs</h5>
                                            <p class="card-text">Des prix transparents et sans frais cachés.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="card text-center h-100">
                                        <div class="card-body">
                                            <i class="bi bi-headset text-primary" style="font-size: 2rem;"></i>
                                            <h5 class="card-title mt-3">Interface simple</h5>
                                            <p class="card-text">Même un petit enfant peut faire une réservation </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <a href="views\vehicle\index.php" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search me-2"></i>Voir les Véhicules Disponibles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>