<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - RentalKu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/navbar.css">
    <link rel="stylesheet" href="../assets/css/footer.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #fff;
        }

        /* Empty Cart Section */
        .empty-cart-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 80vh; /* Membuat konten di tengah layar */
            text-align: center;
        }

        .empty-cart-container i {
            font-size: 80px;
            color: #bdc3c7;
            margin-bottom: 20px;
        }

        .empty-cart-container h2 {
            font-size: 28px;
            margin: 10px 0;
            color: #000;
        }

        .empty-cart-container p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }

        .btn-rent {
            background-color: #0b0c10;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s;
        }

        .btn-rent:hover {
            background-color: #333;
        }
    </style>
</head>
<body>

  <?php
  include '../assets/includes/navbar.php';
  ?>

    <main class="empty-cart-container">
        <i class="fas fa-shopping-cart"></i>
        
        <h2>Keranjang Kosong</h2>
        <p>Belum ada mobil di keranjang Anda</p>
        
        <a href="katalog.php" class="btn-rent">Mulai Menyewa Mobil</a>
    </main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include '../assets/includes/footer.php'; ?>
</body>
</html>