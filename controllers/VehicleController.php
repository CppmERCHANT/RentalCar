<?php 
require_once 'app/config/db.php';

class VehicleController extends Controller {
    public function index() {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vehicle_rental", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->query("SELECT * FROM vehicles WHERE available = 1");
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->view('vehicle/index', ['vehicles' => $vehicles]);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            $this->view('vehicle/index', ['vehicles' => []]);
        }
    }

    public function show($id) {
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=vehicle_rental", "root", "");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
            $stmt = $pdo->prepare("SELECT * FROM vehicles WHERE id = ?");
            $stmt->execute([$id]);
            $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if ($vehicle) {
                $this->view('vehicle/show', ['vehicle' => $vehicle]);
            } else {
                echo "Véhicule introuvable.";
            }
        } catch (PDOException $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }
    
}
