<?php

require_once 'app/core/Model.php';

class Vehicle extends Model {
    public function getAllVehicles() {
        try {
            $stmt = $this->db->query("SELECT * FROM vehicles WHERE available = 1");
            $vehicles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
            if ($vehicles) {
                return $vehicles;
            } else {
                error_log("No vehicles found in the database.");
                return [];
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    

    public function getVehicleById($id) {
        $stmt = $this->db->prepare("SELECT id, make, model, year, color, daily_price, weight, description, image_path FROM vehicles WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
