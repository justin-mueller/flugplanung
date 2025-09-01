<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT r.`key`, r.`fluggebiet`, r.`text`, r.`level`, r.`closed`, r.`solvedText`, 
        r.`created_by`, r.`created_at`, r.`closed_by`, r.`closed_at`,
        CONCAT(c.firstname, " ", c.lastname) as created_by_name,
        CONCAT(cl.firstname, " ", cl.lastname) as closed_by_name
        FROM reparaturen r
        LEFT JOIN mitglieder c ON r.created_by = c.pilot_id
        LEFT JOIN mitglieder cl ON r.closed_by = cl.pilot_id
        ORDER BY r.`created_at` ASC';
$result = Database::query($sql, []);

header('Content-Type: application/json');
echo json_encode($result ?: [], JSON_THROW_ON_ERROR);


