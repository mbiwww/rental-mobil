<?php

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Car.php';
require_once __DIR__ . '/../classes/Rental.php';
require_once __DIR__ . '/../classes/Payment.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/RentalHandler.php';

$db = Database::getInstance()->getConnection();
(new RentalHandler($db))->dispatch();
