<?php

require_once 'app/core/Model.php';

class Reservation extends Model
{
    public function createReservation($user_id, $vehicle_id, $start_date, $end_date, $total_price)
    {
        try {
            $stmt = $this->db->prepare("INSERT INTO reservations (user_id, vehicle_id, start_date, end_date, total_price, status, created_at)
                                        VALUES (?, ?, ?, ?, ?, 'en_attente', NOW())");
            $stmt->execute([$user_id, $vehicle_id, $start_date, $end_date, $total_price]);

            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur de base de données lors de la création de la réservation : " . $e->getMessage());
            return false;
        }
    }

    public function updateVehicleAvailability($vehicle_id)
    {
        try {
            $stmt = $this->db->prepare("UPDATE vehicles SET available = 0 WHERE id = ?");
            $stmt->execute([$vehicle_id]);
            return true;
        } catch (PDOException $e) {
            error_log("Erreur de base de données lors de la mise à jour de la disponibilité du véhicule : " . $e->getMessage());
            return false;
        }
    }

    public function getAllReservations()
    {
        try {
            $stmt = $this->db->query("SELECT * FROM reservations");
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $reservations;
        } catch (PDOException $e) {
            error_log("Erreur de base de données lors de la récupération des réservations : " . $e->getMessage());
            return [];
        }
    }

    public function getReservationsByUser($user_id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM reservations WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Erreur de base de données lors de la récupération des réservations pour l'utilisateur : " . $e->getMessage());
            return []; 
        }
    }

    public function getUserReservations($userId) {
        $sql = "SELECT r.*, v.make, v.model, v.year, v.color 
                FROM reservations r
                JOIN vehicles v ON r.vehicle_id = v.id
                WHERE r.user_id = :user_id
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
