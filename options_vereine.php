<?php
require 'clubs.php';

$options = '';
foreach ($clubs as $id => $name) {
    $options .= sprintf('<option value="%s">%s</option>', $id, htmlentities($name)) . PHP_EOL;
}

return $options;
