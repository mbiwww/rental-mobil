<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

</head>
<body>

<nav class="navbar navbar-rental">
  <div class="container">

    <a href="../../rental-mobil/" class="brand-wrapper">
      <i class="bi bi-car-front-fill brand-icon"></i>
      <span class="brand-text">RentalKu</span>
    </a>

    <div class="nav-right">
      <a href="../../rental-mobil/pages/katalog.php" class="catalog-link">
        <i class="bi bi-grid-3x3-gap-fill me-1"></i> 
        <span>Katalog Mobil</span>
      </a>

      <a href="../../rental-mobil/pages/keranjang.php" class="cart-icon" title="Keranjang Sewa">
        <i class="bi bi-cart3"></i>
      </a>

      <div class="dropdown">
        <a href="#" class="profile-icon dropdown-toggle profile-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false" role="button">
          <div class="avatar-circle">
            J
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-profile" aria-labelledby="profileDropdown">
          <li class="profile-header">
            <div class="profile-name">John Doe</div>
            <div class="profile-email">
              <i class="bi bi-envelope-fill me-1"></i> john@example.com
            </div>
          </li>
          <li><hr class="dropdown-divider my-1"></li>
          <li>
            <a class="dropdown-item dropdown-item-custom" href="#">
              <i class="bi bi-grid-fill"></i> Dashboard Saya
            </a>
          </li>
          <li>
            <a class="dropdown-item dropdown-item-custom" href="../../rental-mobil/login.php">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>