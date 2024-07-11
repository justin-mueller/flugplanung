<?php
require 'clubs.php';

$options = '';
foreach ($clubs as $id => $names) {
    $options .= sprintf('<option value="%s">%s</option>', $id, htmlentities($names['name'])) . PHP_EOL;
}

return $options;
