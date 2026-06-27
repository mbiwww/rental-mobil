<?php

require_once __DIR__ . '/../includes/admin_auth_check.php';

require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/BaseModel.php';
require_once __DIR__ . '/../classes/BankAccount.php';
require_once __DIR__ . '/../classes/handlers/BaseHandler.php';
require_once __DIR__ . '/../classes/handlers/BankAccountHandler.php';

$db = Database::getInstance()->getConnection();
(new BankAccountHandler($db))->dispatch();
