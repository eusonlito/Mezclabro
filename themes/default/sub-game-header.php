<?php defined('BASE_PATH') or die(); ?>

<div class="page-header">
    <h1>
        <a href="<?php echo BASE_WWW; ?>profile.php?id=<?php echo $Game->opponent->id; ?>"><?php
            echo $Game->opponent->name;
        ?></a>

        <small><?php
            __e($Game->game_status);

            if ($Game->active) {
                echo ' ('.($Game->my_turn ? __('Your turn') : __('Opponent turn')).')';
            } else if ($Game->ended_reason === 'EXPIRED') {
                echo ' ('.__('EXPIRED').')';
            } else if (isset($Game->last_turn->type) && ($Game->last_turn->type !== 'PLACE_TILE')) {
                echo ' ('.__($Game->last_turn->type).')';
            }
        ?></small>

        <?php if (isset($Game->messages)) { ?>
        <script src="<?php echo BASE_THEME; ?>js/chat.js" type="text/javascript"></script>

        <a href="#" class="chat-24" title="<?php __e('You have %s chat messages', count($Game->messages)); ?>"><?php
            echo $Game->messages ? count($Game->messages) : '';
        ?></a>
        <?php } ?>

        <p>
            <?php if (FILENAME === 'round.php') { ?>
            <small><a class="label label-inverse" href="<?php echo BASE_WWW; ?>game.php?id=<?php echo $Game->id; ?>">&laquo; <?php __e('Back to game'); ?></a></small>
            <?php } ?>

            <small class="label"><abbr class="timeago" title="<?php echo timeAgo($Game->last_play_date); ?>"><?php echo humanDate($Game->last_play_date); ?></abbr></small>

            <small class="label label-<?php echo ($Game->my_score > $Game->opponent_score) ? 'success' : 'important'; ?>"><?php
                echo $Game->my_score.' / '.$Game->opponent_score;
            ?></small>

            <small class="label label-info"><?php echo $Game->language; ?></small>

            <?php if (isset($Game->last_turn->words)) { ?>
            <small class="label label-info"><?php
                __e('Last words: %s', str_replace('-', ', ', $Game->last_turn->words));

                if (isset($Game->last_turn->turn_points)) {
                    echo ' - '.__('%s points', $Game->last_turn->turn_points);
                }
            ?></small>
            <?php } else if (isset($Game->last_turn->type) && in_array($Game->last_turn->type, array('SWAP', 'PASS'))) { ?>
            <small class="label label-important"><?php
                if ($Game->last_turn->type === 'SWAP') {
                    echo $Game->my_turn ? __('%s has swapped tiles', $Game->opponent->name) : __('You have swapped tiles');
                } else {
                    echo $Game->my_turn ? __('%s has passed the turn', $Game->opponent->name) : __('You have passed your turn');
                }
            ?></small>
            <?php } ?>

            <?php echo getTurnsResume($Game); ?>
        </p>
    </h1>
</div>

<?php if (isset($Game->messages)) { ?>
<div id="modal-chat" class="modal hide">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">×</button>
        <h3><?php __e('Chat'); ?></h3>
    </div>

    <div class="modal-body max-height"></div>

    <form autocomplete="off" method="post" class="form-inline">
        <fieldset class="center">
            <input class="input-xlarge" size="16" type="text" name="message" />
            <button type="submit" class="btn btn-success"><?php __e('Send'); ?></button>
        </fieldset>
    </form>

    <div class="modal-footer">
        <a href="#" class="btn" data-dismiss="modal"><i class="icon-remove"></i> <?php __e('Close'); ?></a>
    </div>
</div>
<?php } ?>
