<?php

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/Car.php';
require_once __DIR__ . '/../classes/CarType.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/CarHandler.php';

$db = Database::getInstance()->getConnection();
(new CarHandler($db))->dispatch();
