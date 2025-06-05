<?php
require 'config/db.php';

$registration_success = false;
$error_message = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $first_name = $_POST['first_name'];
        $last_name = $_POST['last_name'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = 'client';
        $created_at = date('Y-m-d H:i:s');

        $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $check_stmt->execute([$email]);
        $email_exists = $check_stmt->fetchColumn() > 0;

        if ($email_exists) {
            $error_message = "Cet email est déjà utilisé. Veuillez en choisir un autre.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, first_name, last_name, password, role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$username, $email, $first_name, $last_name, $password, $role, $created_at]);
            
            header("Location: login.php?registered=true");
            exit();
        }
    } catch (PDOException $e) {
        $error_message = "Une erreur est survenue lors de l'inscription. Veuillez réessayer.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | AutoRent</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .register-container {
            max-width: 550px;
            width: 100%;
            padding: 1rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
        }
        
        .card-header {
            background-color: #343a40;
            color: white;
            border-radius: 12px 12px 0 0 !important;
            padding: 1.5rem;
            text-align: center;
        }
        
        .form-control {
            padding: 12px;
            border-radius: 8px;
        }
        
        .btn-primary {
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
        }
        
        .card-footer {
            background-color: transparent;
            text-align: center;
            border-top: none;
        }
        
        .brand-logo {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .form-row {
            display: flex;
            gap: 1rem;
        }
        
        @media (max-width: 576px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card">
            <div class="card-header">
                <div class="brand-logo"><i class="bi bi-car-front"></i></div>
                <h2 class="h3 mb-0">IdrisLocation</h2>
                <p class="text-white-50 mb-0">Créer votre compte</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="form-row mb-3">
                        <div class="flex-grow-1">
                            <label for="first_name" class="form-label">
                                <i class="bi bi-person me-2"></i>Prénom
                            </label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        
                        <div class="flex-grow-1">
                            <label for="last_name" class="form-label">
                                <i class="bi bi-person me-2"></i>Nom
                            </label>
                            <input type="text" class="form-control" id="last_name" name="last_name" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">
                            <i class="bi bi-person-badge me-2"></i>Nom d'utilisateur
                        </label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Mot de passe
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required 
                               minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                               title="Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule et un chiffre">
                        <div class="form-text text-muted">
                            <small>Le mot de passe doit contenir au moins 8 caractères, dont une majuscule, une minuscule et un chiffre</small>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-person-plus me-2"></i>S'inscrire
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer py-3">
                <p class="mb-0">Vous avez déjà un compte? <a href="login.php" class="text-primary">Se connecter</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>