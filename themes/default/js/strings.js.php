<?php
header('Content-Type: text/javascript');

require (__DIR__.'/../../../libs/Lito/Mezclabro/Loader-min.php');
?>

var strings = new Array();

strings['waiting_reply'] = '<?php echo str_replace("\'", '&apos;', __('Waiting for reply...')); ?>';
strings['please_wait'] = '<?php echo str_replace("\'", '&apos;', __('Please, wait...')); ?>';
strings['sending'] = '<?php echo str_replace("\'", '&apos;', __('Sending...')); ?>';
strings['no_results'] = '<?php echo str_replace("\'", '&apos;', __('No results for your query')); ?>';
strings['resign'] = '<?php echo str_replace("\'", '&apos;', __('Are you sure that you want resign this game?!?')); ?>';
strings['resign_sure'] = '<?php echo str_replace("\'", '&apos;', __('100% Sure?')); ?>';
strings['server_error'] = '<?php echo str_replace("\'", '&apos;', __('Some error occours with the server response. Please try it again.')); ?>';
strings['screen_updated'] = '<?php echo str_replace("\'", '&apos;', __('This screen has updates. Click here to reload it.')); ?>';
strings['updates'] = '<?php echo str_replace("\'", '&apos;', __('Updates!')); ?>';
strings['your_turn'] = '<?php echo str_replace("\'", '&apos;', __('Your Turn')); ?>';
strings['new_messages'] = '<?php echo str_replace("\'", '&apos;', __('You have new messages!')); ?>';
strings['your_messages'] = '<?php echo str_replace("\'", '&apos;', __('You have %s messages')); ?>';
strings['select_language'] = '<?php echo str_replace("\'", '&apos;', __('You must select a language')); ?>';