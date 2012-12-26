<?php
require (__DIR__.'/libs/Lito/Mezclabro/Loader.php');

$game = isset($_GET['id']) ? $_GET['id'] : null;

require (BASE_PATH.'/aux/game-check.php');

$Game = $Api->getGame($Game->id);

if ($Game->game_status !== 'ENDED') {
    if (isset($_POST['resign']) && ($_POST['resign'] === 'true')) {
        $success = $Api->resignGame();

        if ($success) {
            $Theme->setMessage(__('You have resigned this game'), 'success');

            $Game = $Api->getGame($Game->id, true);
        } else {
            $Theme->setMessage(__('Sorry but some problem occours when try to resign this game'), 'error');
        }
    }
}

$Game->messages = $Api->getChat();

$chat_id = $Game->messages ? md5(end($Game->messages)->date) : '';

$Theme->set('body', basename(__FILE__));

$Theme->meta('title', __('Game versus %s', $Game->opponent->name));

include ($Theme->get('base.php'));
