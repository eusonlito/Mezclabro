<?php
require (__DIR__.'/libs/Lito/Mezclabro/Loader.php');

$game = isset($_GET['id']) ? $_GET['id'] : null;

require (BASE_PATH.'/aux/game-check.php');

$Game = $Api->getGame($Game->id);
$Round = $Api->getRound($Game->id, $_GET['round']);

if ($Round) {
    $Theme->set('body', basename(__FILE__));
} else {
    $Theme->setMessage(__('Some error occours triying to load this round. You sure that this round has already started?'), 'error', true);
}

if (isset($Game->my_turn) && $Game->turn != $_GET['round']) {
    $Game->my_turn = false;
}

if ($Game->game_status !== 'ENDED') {
    if (isset($_POST['play']) && ($_POST['play'] === 'true')) {
        $success = $Api->playGame($_POST);

        if ($success) {
            $Theme->setMessage(__('Your words were set successfully'), 'success');

            $Game = $Api->getGame($Game->id, true);
            $Round = $Api->getRound($Game->id, $_GET['round']);
        } else {
            $Theme->setMessage(__('Some error occours triying to play yours words. Please try it again.'), 'error');
        }
    }
}

$Theme->meta('title', __('Game versus %s', $Game->opponent->name));

include ($Theme->get('base.php'));
