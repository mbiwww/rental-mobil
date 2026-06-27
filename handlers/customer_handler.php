<?php

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/CustomerHandler.php';

$db = Database::getInstance()->getConnection();
(new CustomerHandler($db))->dispatch();
