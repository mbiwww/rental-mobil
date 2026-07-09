<?php
/**
 * Database Seeder for Rental Mobil Transaksi
 * Open this file in your browser or run "php seed.php" in terminal to populate mock data.
 */

require_once __DIR__ . '/classes/Database.php';

try {
    $db = Database::getInstance()->getConnection();

    // Disable foreign key checks to truncate safely
    $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
    $db->exec("TRUNCATE TABLE payments;");
    $db->exec("TRUNCATE TABLE refunds;");
    $db->exec("TRUNCATE TABLE rentals;");
    $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "<h3>Existing rentals and payments truncated successfully!</h3>";

    // Check if user Galeh Wibisono (id = 6) exists, otherwise create it
    $stmt = $db->prepare("SELECT id FROM users WHERE id = 6");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $db->exec("INSERT INTO users (id, name, nik, email, phone, address, password, role) VALUES (
            6, 'Galeh Wibisono', '3312511092', 'glhwbsn270101@gmail.com', '085801040463', 'Batu Ampar, Batam', '$2y$12$fC0YyN0ykPGNMcCQ6G7nA.Azn0lJYW3fXUSdI3ZPcTpppzxYnl95G', 'customer'
        )");
        echo "<p>User Galeh Wibisono created.</p>";
    }

    // Insert 1: Pending Rental + Payment
    $db->exec("INSERT INTO rentals (id, user_id, car_id, start_date, end_date, pickup_location, dropoff_location, total_price, rental_type, driver_cost, status, created_at)
        VALUES (1, 6, 1, '2026-06-10', '2026-06-12', 'Batam Center Ferry Terminal', 'Batam Center Ferry Terminal', 700000.00, 'without_driver', 0.00, 'pending', '2026-06-04 10:00:00')");
    $db->exec("INSERT INTO payments (rental_id, method, bank_account_id, proof_image, status, created_at)
        VALUES (1, 'bank_transfer', 1, 'mock_proof_1.jpg', 'pending', '2026-06-04 10:05:00')");

    // Insert 2: Confirmed Rental + Payment
    $db->exec("INSERT INTO rentals (id, user_id, car_id, start_date, end_date, pickup_location, dropoff_location, total_price, rental_type, driver_cost, status, created_at)
        VALUES (2, 6, 2, '2026-06-15', '2026-06-18', 'Nagoya Hill Mall', 'Nagoya Hill Mall', 1200000.00, 'without_driver', 0.00, 'confirmed', '2026-06-03 09:00:00')");
    $db->exec("INSERT INTO payments (rental_id, method, bank_account_id, proof_image, status, paid_at, created_at)
        VALUES (2, 'bank_transfer', 2, 'mock_proof_2.jpg', 'confirmed', '2026-06-03 09:15:00', '2026-06-03 09:10:00')");

    // Insert 3: Ongoing Rental (Pajero Sport, car_id=3) + Payment
    $db->exec("INSERT INTO rentals (id, user_id, car_id, start_date, end_date, pickup_location, dropoff_location, total_price, rental_type, driver_cost, status, created_at)
        VALUES (3, 6, 3, '2026-06-01', '2026-06-05', 'Hang Nadim Airport', 'Hang Nadim Airport', 4200000.00, 'with_driver', 800000.00, 'ongoing', '2026-05-31 14:00:00')");
    $db->exec("INSERT INTO payments (rental_id, method, bank_account_id, proof_image, status, paid_at, created_at)
        VALUES (3, 'bank_transfer', 1, 'mock_proof_3.jpg', 'confirmed', '2025-05-31 14:30:00', '2026-05-31 14:15:00')");
    // Since it's ongoing, mark car_id = 3 status as 'rented'
    $db->exec("UPDATE cars SET status = 'rented' WHERE id = 3");

    // Insert 4: Completed Rental + Payment
    $db->exec("INSERT INTO rentals (id, user_id, car_id, start_date, end_date, pickup_location, dropoff_location, total_price, rental_type, driver_cost, status, created_at)
        VALUES (4, 6, 4, '2026-05-20', '2026-05-22', 'PT RentalQu Office', 'PT RentalQu Office', 1400000.00, 'without_driver', 0.00, 'completed', '2026-05-19 08:00:00')");
    $db->exec("INSERT INTO payments (rental_id, method, bank_account_id, proof_image, status, paid_at, created_at)
        VALUES (4, 'bank_transfer', 3, 'mock_proof_4.jpg', 'confirmed', '2026-05-19 08:30:00', '2026-05-19 08:15:00')");

    // Insert 5: Cancel Requested Rental
    $db->exec("INSERT INTO rentals (id, user_id, car_id, start_date, end_date, pickup_location, dropoff_location, total_price, rental_type, driver_cost, status, created_at)
        VALUES (5, 6, 1, '2026-06-25', '2026-06-27', 'Batam Center Ferry Terminal', 'Batam Center Ferry Terminal', 700000.00, 'without_driver', 0.00, 'cancel_requested', '2026-06-04 11:00:00')");

    echo "<h3 style='color: green;'>Mock rentals, payments and status synced successfully!</h3>";
    echo "<p><a href='admin/transaksi.php'>Go to Admin Transactions Page</a></p>";

} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
