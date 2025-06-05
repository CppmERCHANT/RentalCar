<?php 
require_once 'app/config/db.php';

class ReservationController extends Controller
{
    public function create()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            die("Méthode non autorisée.");
        }

        if (!isset($_SESSION['user_id'])) {
            die("Vous devez être connecté pour réserver.");
        }

        $user_id = $_SESSION['user_id'];
        $vehicle_id = $_POST['vehicle_id'];
        $start_date = $_POST['start_date'];
        $end_date = $_POST['end_date'];

        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vehicle_rental", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ? AND available = 1");
            $stmt->execute([$vehicle_id]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vehicle) {
                die("Véhicule non disponible.");
            }

            $start = new DateTime($start_date);
            $end = new DateTime($end_date);
            $days = $start->diff($end)->days + 1; 
            if ($days <= 0) {
                die("Les dates ne sont pas valides.");
            }

            $total_price = $days * $vehicle['daily_price'];

            $stmt = $pdo->prepare("INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, total_price, status, created_at)
                                   VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())");
            $stmt->execute([$user_id, $vehicle_id, $start_date, $end_date, $total_price]);

            $stmt = $pdo->prepare("UPDATE vehicles SET available = 0 WHERE id = ?");
            $stmt->execute([$vehicle_id]);

            echo "Réservation confirmée !";
            echo "<a href='/vehicles'>Retour à la liste des véhicules</a>";

        } catch (PDOException $e) {
            error_log("Erreur de base de données : " . $e->getMessage());
            echo "Erreur lors de la réservation. Veuillez réessayer.";
        }
    }
    
}
