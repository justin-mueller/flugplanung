<?php

/**
 * Automated Mail Trigger for the completed half-year duty schedule.
 *
 * This wrapper routes through sendMail.php and only keeps HDGF-internal
 * recipients who have future duties assigned.
 */

require_once __DIR__ . '/vendor/autoload.php';

use JustinMueller\Flugplanung\Database;
use JustinMueller\Flugplanung\Helper;

Helper::loadConfiguration();

$secret = Helper::$configuration['mailSecret'] ?? Helper::$configuration['newsletterSecret'] ?? null;

if (!isset($_GET['key']) || $_GET['key'] !== $secret) {
    http_response_code(403);
    exit("Forbidden: Invalid or missing key");
}

$_POST['sender_email'] = Helper::$configuration['flugplanungFrom'];
$_POST['mail_file'] = 'dienste_halbjahr.html';
$_POST['user_id_from'] = isset($_POST['user_id_from'])
    ? intval($_POST['user_id_from'])
    : (isset($_GET['from']) ? intval($_GET['from']) : 0);
$_POST['user_id_to'] = isset($_POST['user_id_to'])
    ? intval($_POST['user_id_to'])
    : (isset($_GET['to']) ? intval($_GET['to']) : 99999);
$_POST['internal_only'] = '1';
$configClubId = Helper::$configuration['clubId'] ?? null;

$_GET['test'] = isset($_GET['test']) ? $_GET['test'] : 'false';

$defaultTestRecipient = 'register@simulux.de';
if (isset($_POST['test_recipient'])) {
    $_POST['test_recipient'] = $_POST['test_recipient'];
} elseif (isset($_GET['test_recipient'])) {
    $_POST['test_recipient'] = $_GET['test_recipient'];
} elseif (isset($_GET['test']) && $_GET['test'] === 'true') {
    $_POST['test_recipient'] = $defaultTestRecipient;
}

$mailRecipientFilter = function (array $recipients) use ($configClubId): array {
    if (empty($recipients)) {
        return [
            'recipients' => [],
            'personalizations' => [],
            'excluded' => [],
            'description' => 'Nur Empfaenger mit zukuenftigen Diensten'
        ];
    }

    $internalRecipients = [];
    $excludedRecipients = [];
    foreach ($recipients as $recipient) {
        if ($configClubId !== null && (int)($recipient['verein'] ?? 0) !== (int)$configClubId) {
            $excludedRecipients[] = $recipient;
            continue;
        }
        $internalRecipients[] = $recipient;
    }

    if (empty($internalRecipients)) {
        return [
            'recipients' => [],
            'personalizations' => [],
            'excluded' => $excludedRecipients,
            'description' => 'Nur HDGF-interne Empfaenger mit zukuenftigen Diensten'
        ];
    }

    $pilotIds = array_values(array_unique(array_map(
        static fn($recipient) => (int)$recipient['pilot_id'],
        $internalRecipients
    )));
    $placeholders = implode(',', array_fill(0, count($pilotIds), '?'));

    $sql = "SELECT d.flugtag, d.pilot_id, d.windenfahrer, d.startleiter
            FROM dienste d
            WHERE d.pilot_id IN ({$placeholders})
            AND d.flugtag >= CURDATE()
            ORDER BY d.pilot_id, d.flugtag";

    $duties = Database::query($sql, $pilotIds);
    $dutiesByPilot = [];
    if (is_array($duties)) {
        foreach ($duties as $duty) {
            $pilotId = (int)$duty['pilot_id'];
            if (!isset($dutiesByPilot[$pilotId])) {
                $dutiesByPilot[$pilotId] = [];
            }
            $dutiesByPilot[$pilotId][] = $duty;
        }
    }

    $filteredRecipients = [];
    $personalizations = [];

    foreach ($internalRecipients as $recipient) {
        $pilotId = (int)$recipient['pilot_id'];
        if (empty($dutiesByPilot[$pilotId])) {
            $excludedRecipients[] = $recipient;
            continue;
        }

        $filteredRecipients[] = $recipient;
        $personalizations[$pilotId] = [
            '{{dienste}}' => formatDiensteList($dutiesByPilot[$pilotId])
        ];
    }

    return [
        'recipients' => $filteredRecipients,
        'personalizations' => $personalizations,
        'excluded' => $excludedRecipients,
        'description' => 'Nur HDGF-interne Empfaenger mit zukuenftigen Diensten'
    ];
};

function formatDiensteList(array $duties): string
{
    $items = [];
    foreach ($duties as $duty) {
        $roles = [];
        if (!empty($duty['windenfahrer'])) {
            $roles[] = 'Windenfahrer';
        }
        if (!empty($duty['startleiter'])) {
            $roles[] = 'Startleiter';
        }

        $date = date('d.m.Y', strtotime($duty['flugtag']));
        $roleText = !empty($roles) ? ' - ' . htmlspecialchars(implode(', ', $roles)) : '';
        $items[] = '<li>' . htmlspecialchars($date) . $roleText . '</li>';
    }

    return '<ul class="dienste-list">' . implode('', $items) . '</ul>';
}

require_once __DIR__ . '/sendMail.php';
