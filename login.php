<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user; 
        header("Location: /app/dashboard.php");
        exit();
    } else {
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | IdrisLocation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 450px;
            width: 100%;
            padding: 2rem;
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
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <div class="brand-logo"><i class="bi bi-car-front"></i></div>
                <h2 class="h3 mb-0">IdrisLocation</h2>
                <p class="text-white-50 mb-0">Connexion à votre compte</p>
            </div>
            
            <div class="card-body p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
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
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card-footer py-3">
                <p class="mb-0">Vous n'avez pas de compte? <a href="register.php" class="text-primary">S'inscrire</a></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>