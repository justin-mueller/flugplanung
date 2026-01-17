<?php

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

require_once __DIR__ . '/vendor/autoload.php';

Helper::loadConfiguration();
Helper::checkLogin();
Database::connect();

$startDate = $_GET['startDate'];
$endDate = $_GET['endDate'];
$clubId = Helper::$configuration['clubId'];

// Step 1: Get all flugtage in the date range
$flugtageResult = Database::query(
    "SELECT datum FROM flugtage WHERE datum BETWEEN :startDate AND :endDate ORDER BY datum",
    ['startDate' => $startDate, 'endDate' => $endDate]
);

// Step 2: Get all members for this club
$membersResult = Database::query(
    "SELECT pilot_id, firstname, lastname, windenfahrer, max_dienste_halbjahr FROM mitglieder WHERE verein = :clubId",
    ['clubId' => $clubId]
);

// Step 3: Get all wishes in the date range for this club's members
$wishesResult = Database::query(
    "SELECT dw.pilot_id, dw.datum, dw.wunsch 
     FROM dienste_wuensche dw
     INNER JOIN mitglieder m ON dw.pilot_id = m.pilot_id
     WHERE dw.datum BETWEEN :startDate AND :endDate AND m.verein = :clubId",
    ['startDate' => $startDate, 'endDate' => $endDate, 'clubId' => $clubId]
);

// Step 4: Get all entered dienste in the date range
$diensteResult = Database::query(
    "SELECT d.flugtag, d.pilot_id, d.windenfahrer, d.startleiter 
     FROM dienste d
     INNER JOIN mitglieder m ON d.pilot_id = m.pilot_id
     WHERE d.flugtag BETWEEN :startDate AND :endDate AND m.verein = :clubId",
    ['startDate' => $startDate, 'endDate' => $endDate, 'clubId' => $clubId]
);

// Build lookup structures for fast access
$members = [];
foreach ($membersResult as $member) {
    $members[$member['pilot_id']] = [
        'pilot_id' => (int)$member['pilot_id'],
        'firstname' => $member['firstname'],
        'lastname' => $member['lastname'],
        'windenfahrer' => (int)$member['windenfahrer'],
        'max_dienste_halbjahr' => $member['max_dienste_halbjahr'] !== null ? (int)$member['max_dienste_halbjahr'] : null
    ];
}

// Wishes indexed by date => pilot_id => wunsch value
$wishes = [];
foreach ($wishesResult as $wish) {
    $datum = $wish['datum'];
    $pilotId = (int)$wish['pilot_id'];
    if (!isset($wishes[$datum])) {
        $wishes[$datum] = [];
    }
    $wishes[$datum][$pilotId] = (int)$wish['wunsch'];
}

// Dienste indexed by date => array of entries
$dienste = [];
foreach ($diensteResult as $dienst) {
    $flugtag = $dienst['flugtag'];
    if (!isset($dienste[$flugtag])) {
        $dienste[$flugtag] = [];
    }
    $dienste[$flugtag][] = [
        'pilot_id' => (int)$dienst['pilot_id'],
        'windenfahrer' => (int)$dienst['windenfahrer'],
        'startleiter' => (int)$dienst['startleiter']
    ];
}

// Build the response data
$data = [];

foreach ($flugtageResult as $flugtag) {
    $datum = $flugtag['datum'];
    
    $startleiterOptionen = [];
    $windenfahrerOptionen = [];
    $enteredStartleiter = [];
    $enteredWindenfahrer = [];
    
    // Build options for each role, adding wish indicators
    foreach ($members as $pilotId => $member) {
        $fullName = $member['firstname'] . ' ' . $member['lastname'];
        
        // Check if this pilot has a wish for this date
        $wishSuffix = '';
        if (isset($wishes[$datum][$pilotId])) {
            $wishSuffix = $wishes[$datum][$pilotId] === 1 ? '+' : '-';
        }
        
        $pilotOption = [
            'name' => $fullName . $wishSuffix,
            'id' => (string)$pilotId,
            'max_dienste_halbjahr' => $member['max_dienste_halbjahr']
        ];
        
        // windenfahrer = 0 means this member is a Startleiter candidate
        // windenfahrer = 1 means this member is a Windenfahrer candidate
        if ($member['windenfahrer'] === 0) {
            $startleiterOptionen[] = $pilotOption;
        } else {
            $windenfahrerOptionen[] = $pilotOption;
        }
    }
    
    // Build list of entered pilot IDs for this date
    if (isset($dienste[$datum])) {
        foreach ($dienste[$datum] as $dienst) {
            // Use the specific column: startleiter = 1 means entered as Startleiter
            if ($dienst['startleiter'] === 1) {
                $enteredStartleiter[] = (string)$dienst['pilot_id'];
            }
            // windenfahrer = 1 means entered as Windenfahrer
            if ($dienst['windenfahrer'] === 1) {
                $enteredWindenfahrer[] = (string)$dienst['pilot_id'];
            }
        }
    }
    
    $data[] = [
        'date' => $datum,
        'startleiterOptionen' => $startleiterOptionen,
        'windenfahrerOptionen' => $windenfahrerOptionen,
        'startleiter' => $enteredStartleiter,
        'windenfahrer' => $enteredWindenfahrer
    ];
}

header('Content-Type: application/json');
echo json_encode($data, JSON_THROW_ON_ERROR);
