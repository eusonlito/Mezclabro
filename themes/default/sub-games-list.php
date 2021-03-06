<?php defined('BASE_PATH') or die(); ?>

<div class="row">
    <?php
    foreach ($games as $Game) {
        $Api->setLanguage($Game->language);
    ?>
    <div class="span4">
        <div class="well">
            <div class="row-fluid">
                <div class="span3">
                    <?php if ($Game->opponent->avatar) { ?>

                    <img src="<?php echo $Game->opponent->avatar; ?>" width="50" height="50" />

                    <?php } else { ?>

                    <div class="tile-50">
                        <span class="letter"><?php echo substr($Game->opponent->name, 0, 1); ?></span>
                    </div>

                    <?php } ?>
                </div>

                <div class="span9">
                    <h4>
                        <a href="<?php echo BASE_WWW; ?>game.php?id=<?php echo $Game->id; ?>">
                            <?php echo $Game->opponent->name; ?>

                            <?php if ($Game->my_message_alerts > 0) { ?>
                            <span class="chat-16" title="<?php __e('You have %s new chat messages', $Game->my_message_alerts); ?>"><?php
                                echo $Game->my_message_alerts;
                            ?></span>
                            <?php } ?>
                        </a>
                    </h4><?php

                    ?><small class="label label-info"><?php echo $Game->language; ?></small><?php

                    ?><small class="label"><abbr class="timeago" title="<?php echo timeAgo($Game->last_play_date); ?>"><?php echo humanDate($Game->last_play_date); ?></abbr></small><?php

                    ?><strong class="label label-<?php echo ($Game->my_score > $Game->opponent_score) ? 'success' : 'important'; ?>"><?php
                        echo $Game->my_score.' / '.$Game->opponent_score;
                    ?></strong>

                    <?php echo getTurnsResume($Game); ?>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
</div>
