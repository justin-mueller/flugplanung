<?php
require 'clubs.php';

foreach ($clubs as $id => $name) {
    echo sprintf('<option value="%s">%s</option>', $id, htmlentities($name));
}
