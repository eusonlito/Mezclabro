<?php
if (!isset($_POST['filter']) || !$_POST['filter']) {
    die();
}

require (__DIR__.'/../libs/Lito/Mezclabro/Loader.php');

if (!isAjax()) {
    die();
}

$users = $Api->searchUsers($_POST['filter']);

ob_start();

if ($users) {
    include ($Theme->get('sub-users-list.php'));
} else {
    echo '<h3 class="span12 center">'.__('No users founded...').'</h3>';
}

$html = ob_get_contents();

ob_end_clean();

dieJson(array('html' => $html));
