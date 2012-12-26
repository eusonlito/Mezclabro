$(document).ready(function () {
    'use strict';

    $('#new-game').on('click', '.well.user', function () {
        $('input[name="user_id"]', $('#new-game')).val($(this).data('id'));

        $('#new-game .well.user').css('background-color', 'whiteSmoke');
        $(this).css('background-color', 'yellowGreen');

        $('button[type="submit"]', $(this).parents('div.tab-pane')).attr('disabled', false);
    });

    $('#new-game').submit(function () {
        if ($('input[name="language"]:checked', $(this)).length !== 1) {
            alert(strings['select_language']);
            return false;
        }

        return true;
    });

    $('input[type="checkbox"][name="checkall"]').on('change', function () {
        $('input[type="checkbox"]' + $(this).data('related')).attr('checked', $(this).is(':checked'));

        setPoints();
    });

    $('#game-form input[type="checkbox"]').on('change', function () {
        setPoints();
    });

    $('#game-form').submit(function () {
        $('#modal-confirm .modal-header').hide();
        $('#modal-confirm .modal-footer').hide();

        $('#modal-confirm .modal-body').html(
            '<h2 class="center"><img src="' + BASE_THEME + 'images/loading.gif" />' +
            '<span class="offset05">' + strings['sending'] + '<span></h2>'
        );

        return true;
    });

    $('.filter-users button[type="submit"]').click(function (e) {
        var $this = $(this);

        if (($this.data('url') === undefined) || ($this.data('filtered') === undefined)) {
            return true;
        }

        e.preventDefault();

        var $filtered = $($this.data('filtered'));

        if ($('input[type="text"][name="search"]').val().length > 0) {
            $filtered.html('<div class="span12 center"><img src="' + BASE_THEME + 'images/loading.gif" /></div>');

            $this.attr('disabled', 'disabled');

            $.post($(this).data('url'), {
                filter: $('input[type="text"][name="search"]').val()
            }, function (response) {
                $this.attr('disabled', false);

                if (!checkResponse(response, $filtered)) {
                    return false;
                }

                $filtered.html(response.html);
            });
        }

        return false;
    }).keydown(function (e) {
        if (e.which === 13) {
            e.preventDefault();
        }

        return false;
    });

    $('.filter-list').keyup(function (e) {
        if (e.keyCode === 27) {
            $(this).val('');

            $($(this).data('filtered')).find('input[type="checkbox"]').attr('checked', 'checked');

            $($(this).data('filtered')).show();

            return false;
        }

        var filter = $(this).val();
        var length = $(this).val().length;

        if (length > 0) {
            $($(this).data('filtered')).each(function () {
                var $this = $(this);

                if ($this.text().indexOf(filter) !== -1) {
                    $this.find('input[type="checkbox"]').attr('checked', 'checked');
                    $this.show();
                } else {
                    $this.find('input[type="checkbox"]').attr('checked', false);
                    $this.hide();
                }
            });
        } else {
            $($(this).data('filtered')).find('input[type="checkbox"]').attr('checked', 'checked');
            $($(this).data('filtered')).show();
        }

        setPoints();
    }).keydown(function (e) {
        if (e.which === 13) {
            e.preventDefault();
        }
    });

    $('a[data-action="profile-new-game"]').click(function () {
        var $modal = $('#modal-profile-new-game');

        $modal.modal();

        $('input[name="language"]', $modal).click(function () {
            $('button[type="submit"]', $modal).attr('disabled', false);
        });

        return false;
    });

    if ((typeof(UPDATED) !== 'undefined') && (UPDATED !== '')) {
        var just_updated = new Array();

        var pushInterval = setInterval(function () {
            $.ajax({
                type: 'POST',
                data: 'u=' + UPDATED,
                url: BASE_WWW + 'ajax/push.php',
                success: function (response) {
                    if (!checkResponse(response)) {
                        return false;
                    }

                    if ((response.length === 0) || (response.length === just_updated.length)) {
                        return true;
                    }

                    var $chat_layer = $('#modal-chat .modal-body');
                    var length = response.length;
                    var activate = true;

                    for (var i = 0; i < length; i++) {
                        if ((response[i].type === 'message') && GAME_ID && (GAME_ID === response[i].id)) {
                            if (($chat_layer.length > 0) && $chat_layer.is(':visible')) {
                                activate = false;
                            }

                            break;
                        }
                    }

                    if (activate) {
                        $('#updates a span').text(strings['your_turn'] + ' (' + length + ')');

                        $('#updates > a').addClass('active');

                        if ($('#updates ul li span').length) {
                            $('#updates ul li span').parent().remove();
                        }
                    }

                    if (document.title.match(/^\([0-9]+\)/)) {
                        document.title = document.title.replace(/^\([0-9]+\)/, '(' + length + ')');
                    } else {
                        document.title = '(' + length + ') ' + document.title;
                    }

                    var key = '';

                    for (var i = 0; i < length; i++) {
                        if ((response[i].type === 'message') && GAME_ID && (GAME_ID === response[i].id)) {
                            updateChat();

                            if (!activate) {
                                continue;
                            }
                        }

                        key = response[i].key;

                        if ($.inArray(key, just_updated) === -1) {
                            if ($('#updates li#updated-' + key).length) {
                                $('#updates li#updated-' + key).remove();
                            }

                            $('#updates ul').prepend(
                                '<li id="updated-' + key + '">' +
                                '<a href="' + response[i].link + '"><strong>' +
                                response[i].text + '</strong></li>'
                            );

                            just_updated.push(key);
                        }
                    }
                }
            });
        }, 15000);
    }

    $('#round-timeout-clock').countdown({
        image: BASE_THEME + 'images/digits.png',
        startTime: '120',
        format: 'sss',
        continuous: true,
        timerEnd: function () {
            $('#round-timeout-alert').slideDown();
        }
    });

    $('abbr.timeago').timeago();
});

function checkResponse (response, content) {
    if (!response.error) {
        return true;
    }

    if (content) {
        var html = response.html ? response.html : strings['server_error'];
        content.html('<div class="alert alert-error"><div>' + html + '</div></div>');
    }

    return false;
}

function setPoints () {
    var points = 0;

    $('input[type="checkbox"][name="words\[\]"]').each(function () {
        if ($(this).is(':checked') && $(this).is(':visible')) {
            points += parseInt($(this).parent('label').find('span').text());
        }
    });

    var html = $('#button-confirm').html();

    $('#button-confirm').html(html.replace(/[0-9]+/, points));

    html = $('#modal-confirm div.modal-body').html();

    $('#modal-confirm div.modal-body').html(html.replace(/[0-9]+/, points));

    html = $('#playinfor-alert').html();

    $('#playinfor-alert').html(html.replace(/[0-9]+/, points));

    if (points === 0) {
         $('#button-confirm').attr('disabled', 'disabled');
    } else {
        $('#button-confirm').attr('disabled', false);
    }
}
