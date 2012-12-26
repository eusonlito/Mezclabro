<?php defined('BASE_PATH') or die(); ?>

<?php include ($Theme->get('sub-game-header.php')); ?>

<table class="table table-hover">
    <thead>
        <tr>
            <th class="center"><?php __e('My Points'); ?></th>
            <th class="center"><?php __e('Round'); ?></th>
            <th class="center"><?php __e('Opponent Points'); ?></th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($Game->turns as $turn => $points) { ?>
        <tr>
            <td class="center"><?php echo $points[0]; ?></td>

            <td class="center">
                <strong><?php
                if (($Game->turn === 0) || ($Game->turn > $turn)) {
                    echo '<a href="round.php?id='.$Game->id.'&amp;round='.($turn + 1).'">'.__('Round %s', $turn + 1).'</a>';
                } else {
                    __e('Round %s', $turn + 1);
                }
                ?></strong>
            </td>

            <td class="center"><?php echo $points[1]; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
