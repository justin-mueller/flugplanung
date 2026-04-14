<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$sql = 'SELECT r.`key`, r.`site_index`, r.`text`, r.`level`, r.`closed`, r.`solvedText`,
        r.`created_by`, r.`created_at`, r.`closed_by`, r.`closed_at`,
        CONCAT(c.firstname, " ", c.lastname) as created_by_name,
        CONCAT(cl.firstname, " ", cl.lastname) as closed_by_name
        FROM reparaturen r
        LEFT JOIN mitglieder c ON r.created_by = c.pilot_id
        LEFT JOIN mitglieder cl ON r.closed_by = cl.pilot_id
        ORDER BY r.`created_at` ASC';
$result = Database::query($sql, []);

$shorthands = Helper::getSiteShorthands();
$data = [];
if (is_array($result)) {
    foreach ($result as $row) {
        $row['fluggebiet'] = $shorthands[(int)$row['site_index']] ?? '?';
        $data[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($data, JSON_THROW_ON_ERROR);
