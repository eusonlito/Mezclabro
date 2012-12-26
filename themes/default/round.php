<?php defined('BASE_PATH') or die(); ?>

<?php include ($Theme->get('sub-game-header.php')); ?>

<div class="row">
    <div class="span3 relative">
        <table id="round-table">
            <?php echo $Api->getBoard(); ?>
        </table>

        <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>

        <div id="round-timeout-clock"></div>

        <div id="round-timeout-alert" class="alert alert-success center mt-20 hide">
            <strong><?php __e('It\'s time to play!'); ?></strong>
        </div>

        <?php } ?>
    </div>

    <div class="span9 tabbable">
        <ul class="nav nav-tabs mt-20">
            <li class="active"><a href="#tab-suggested-words" data-toggle="tab"><?php __e('Suggestions'); ?></a></li>
            <?php if (isset($Round->my_turn)) { ?><li><a href="#tab-my-words" data-toggle="tab"><?php __e('Your words'); ?></a></li><?php } ?>
        </ul>

        <div class="tab-content">
            <div class="tab-pane active" id="tab-suggested-words">
                <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>
                <div id="playinfor-alert" class="alert alert-success center">
                    <?php __e('You are playing for <strong>%s</strong> points', $Round->total_points); ?>
                </div>
                <?php } ?>

                <div class="row-fluid">
                    <div class="control-group span5 offset1">
                        <input type="text" class="filter-list large input-block-level" data-filtered=".suggestion-words-list li" value="" placeholder="<?php __e('Filter suggested words'); ?>">
                    </div>

                    <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>
                    <div class="span6">
                        <label class="checkbox inline"><input type="checkbox" name="checkall" data-related="[name='words\[\]']" checked="checked" /> <?php __e('Check/Uncheck all'); ?></label>
                    </div>
                    <?php } ?>
                </div>

                <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>
                <form id="game-form" action="?id=<?php echo $Game->id; ?>&amp;round=<?php echo $Game->turn; ?>" method="post" class="form-horizontal">
                <?php } ?>

                <ul class="suggestion-words-list columns">
                    <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>

                    <?php foreach ($Round->board_words as $word) { ?>
                    <li><label><input type="checkbox" name="words[]" value="<?php echo $word['word']; ?>" checked="checked" /> <?php echo $word['word'].' <span class="pull-right small">'.$word['points'].'</span>'; ?><label></li>
                    <?php } ?>

                    <?php } else { ?>

                    <?php foreach ($Round->board_words as $word) { ?>
                    <li><?php echo $word['word'].' <span class="pull-right small">'.$word['points'].'</span>'; ?></li>
                    <?php } ?>

                    <?php } ?>
                </ul>

                <?php if (isset($Game->my_turn) && $Game->my_turn) { ?>
                <div class="form-actions">
                    <a href="#modal-confirm" id="button-confirm" data-toggle="modal" class="btn btn-large btn-success">
                        <i class="icon-ok icon-white"></i> <?php __e('Play for %s points!', $Round->total_points); ?>
                    </a>
                </div>

                <div id="modal-confirm" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">×</button>
                        <h3><?php __e('Confirm your move'); ?></h3>
                    </div>

                    <div class="modal-body">
                        <?php __e('Are you sure do you want to play for %s points?', $Round->total_points); ?>
                    </div>

                    <div class="modal-footer">
                        <a href="#" class="btn" data-dismiss="modal"><i class="icon-remove"></i> <?php __e('Cancel'); ?></a>

                        <button type="submit" name="play" value="true" class="btn btn-large btn-primary">
                            <i class="icon-ok icon-white"></i> <?php __e('Confirm'); ?>
                        </button>
                    </div>
                </div>

                </form>
                <?php } ?>
            </div>

            <?php if (isset($Round->my_turn)) { ?>
            <div class="tab-pane" id="tab-my-words">
                <div class="control-group">
                    <input type="text" class="span5 filter-list" data-filtered=".my-words-list li" value="" placeholder="<?php __e('Filter my words'); ?>">
                </div>

                <ul class="my-words-list columns">
                    <?php foreach ($Round->my_turn->words as $word) { ?>
                    <li><?php echo $word['word'].' <span class="pull-right small">'.$word['points'].'</span>'; ?></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
