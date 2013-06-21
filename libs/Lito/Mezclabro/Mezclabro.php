<?php
namespace Lito\Mezclabro;

defined('BASE_PATH') or die();

class Mezclabro {
    private $server = 'http://api.mezcladitos.com/api/';
    private $logged = false;
    private $user = 0;
    private $session = '';
    private $language = '';
    private $languages = array();
    private $points = array();
    private $quantity = array();
    private $games = array();
    private $board_points = array();

    private $Game;
    private $Round;
    private $Cookie;
    private $Curl;
    private $Debug;
    private $Timer;

    public $Cache;

    public function __construct ()
    {
        $this->setLanguages();
    }

    public function setTimer ($Timer)
    {
        $this->Timer = &$Timer;
    }

    public function setCache ($Cache)
    {
        $this->Cache = $Cache;
    }

    public function setDebug ($Debug, $function)
    {
        if (is_object($Debug) && method_exists($Debug, $function)) {
            $Debug->function = $function;
            $this->Debug = $Debug;
        }
    }

    public function debug ($text, $trace = true)
    {
        if ($this->Debug) {
            $this->Debug->{$this->Debug->function}($text, $trace);
        }
    }

    public function setCurl ($Curl)
    {
        $this->Curl = $Curl;

        $this->Curl->init($this->server);

        $this->Curl->setOption(CURLOPT_USERAGENT, 'Android/SDK-16 Package:com.etermax.wordcrack.lite/Version:1.4.1');
        $this->Curl->setOption(CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Connection: Keep-Alive'
        ));

        $this->Curl->setJson(true);
    }

    public function setCookie ($Cookie)
    {
        $this->Cookie = $Cookie;
    }

    public function setLanguages ()
    {
        $this->languages = array();

        foreach (glob(BASE_PATH.'languages/*', GLOB_ONLYDIR) as $language) {
            if (is_file($language.'/points.php')) {
                $this->languages[] = basename($language);
            }
        }
    }

    public function setLanguage ($language)
    {
        $this->language = '';

        $language = mb_strtolower($language);

        if (in_array($language, $this->languages, true)) {
            $this->language = $language;
        }
    }

    public function getLanguages ()
    {
        return $this->languages;
    }

    public function getLanguage ()
    {
        return $this->language;
    }

    public function reload ()
    {
        if ($this->Cache) {
            $this->Cache->reload(true);
        }

        if ($this->Curl->Cache) {
            $this->Curl->Cache->reload(true);
        }

        unset($_GET['reload']);
    }

    public function clearData ()
    {
        $this->logged = false;
        $this->user = 0;
        $this->session = '';
        $this->games = array();
    }

    public function logged ()
    {
        if ($this->logged) {
            return $this->logged;
        }

        $cookie = $this->Cookie->get();

        if (!isset($cookie['user']) || empty($cookie['user'])
        || !isset($cookie['session']) || empty($cookie['session'])) {
            return false;
        }

        return $this->loginSession($cookie['user'], $cookie['session']);
    }

    public function login ($user, $password)
    {
        if (empty($user) || empty($password)) {
            return false;
        }

        $this->clearData();

        $this->Curl->setHeader(true);
        $this->Curl->setOption(CURLOPT_FAILONERROR, false);

        $Login = $this->Curl->post('login', array(
            'email' => $user,
            'password' => $password
        ));

        $this->Curl->setHeader(false);

        if (!is_object($Login) || !isset($Login->id)) {
            return $Login;
        }

        $header = $this->Curl->getHeader();

        preg_match('/ap_session=[a-z0-9]+/', $header, $session);

        $this->logged = true;
        $this->user = $Login->id;
        $this->session = $session[0];

        $this->Curl->setCookie($this->session);

        $this->Cookie->set(array(
            'user' => $this->user,
            'session' => $this->session
        ));

        return $this->logged;
    }

    public function loginSession ($user, $session)
    {
        $this->Curl->setCookie($session);

        $this->clearData();

        $this->logged = true;
        $this->user = $user;
        $this->session = $session;

        $cookie = $this->Cookie->get();

        if (!isset($cookie['user']) || empty($cookie['user'])
        || !isset($cookie['session']) || empty($cookie['session'])) {
            $this->Cookie->set(array(
                'user' => $this->user,
                'session' => $this->session
            ));
        }

        return $this->logged;
    }

    public function loginFacebook ($email)
    {
        $this->clearData();

        $User = $this->Curl->post('emails', $email);

        if (!is_object($User) || ($User->total !== 1)) {
            return false;
        }

        $Login = $this->Curl->post('login', array(
            'id' => $User->list[0]->id,
            'username' => $User->list[0]->username,
            'email' => $User->list[0]->email,
            'facebook_id' => $User->list[0]->facebook_id,
        ));

        if (!is_object($Login) || !isset($Login->id)) {
            return false;
        }

        $this->logged = true;
        $this->user = $Login->id;
        $this->session = $Login->session->session;

        $this->Curl->setCookie($this->session);

        $this->Cookie->set(array(
            'user' => $this->user,
            'session' => $this->session
        ));

        return $this->logged;
    }

    public function logout ()
    {
        $this->Cookie->set(array(
            'user' => '',
            'session' => ''
        ));
    }

    private function _loggedOrDie ()
    {
        if (empty($this->logged)) {
            throw new \Exception('You are not logged');
        }
    }

    public function getUser ($user = '')
    {
        $this->_loggedOrDie();

        if ($user) {
            $User = $this->Curl->get('users/'.$this->user.'/users/'.$user);
        } else {
            $User = $this->Curl->get('users/'.$this->user);
        }

        if (!is_object($User) || empty($User->id)) {
            return false;
        }

        $User = $this->setFacebookInfo($User);

        return $User;
    }

    public function getUserLogged ()
    {
        $this->_loggedOrDie();

        return $this->user;
    }

    public function setFacebookInfo ($User)
    {
        if (!is_object($User)) {
            return $User;
        }

        $User->facebook_public = true;

        if (isset($User->facebook_id) && isset($User->facebook_name) && $User->fb_show_name && $User->facebook_name) {
            $User->name = $User->facebook_name;
        } else {
            $User->name = $User->username;
            $User->facebook_public = false;
        }

        if (isset($User->facebook_id) && $User->fb_show_picture) {
            $User->avatar = 'http://graph.facebook.com/'
                .$User->facebook_id
                .'/picture';
        } else {
            $User->avatar = '';
            $User->facebook_public = false;
        }

        return $User;
    }

    public function myUser ($user = '')
    {
        $this->_loggedOrDie();

        return $user ? ($this->user == $user) : $this->user;
    }

    public function addFriend ($user)
    {
        $this->_loggedOrDie();

        return $this->Curl->post('users/'.$this->user.'/favorites', array(
            'id' => $user
        ));
    }

    public function removeFriend ($user)
    {
        $this->_loggedOrDie();

        return $this->Curl->custom('DELETE', 'users/'.$this->user.'/favorites/'.$user);
    }

    public function getFriends ()
    {
        $this->_loggedOrDie();

        $Friends = $this->Curl->get('users/'.$this->user.'/friends');

        if (!is_object($Friends) || empty($Friends->list)) {
            return array();
        }

        foreach ($Friends->list as &$Friend) {
            $Friend->friend = $this->setFacebookInfo($Friend->friend);
        }

        unset($Friend);

        return $Friends->list;
    }

    public function searchUsers ($filter)
    {
        $this->_loggedOrDie();

        $filter = urlencode($filter);

        $Users = $this->Curl->get('search?email='.$filter.'&username='.$filter);

        if (!is_object($Users) || empty($Users->list)) {
            return array();
        }

        foreach ($Users->list as &$User) {
            $User = $this->setFacebookInfo($User);
        }

        unset($User);

        return $Users->list;
    }

    public function loadGames ()
    {
        $this->_loggedOrDie();

        $this->Timer->mark('INI: Mezclabro->loadGames');

        $this->games = array(
            'all' => array(),
            'pending' => array(),
            'active' => array(),
            'ended' => array(),
            'turn' => array(),
            'waiting' => array()
        );

        $Games = $this->Curl->get('users/'.$this->user.'/games');

        if (!is_object($Games) || empty($Games->total)) {
            return array();
        }

        usort($Games->list, function ($a, $b) {
            return (strtotime($a->last_play_date) > strtotime($b->last_play_date)) ? -1 : 1;
        });

        foreach ($Games->list as $Game) {
            if ($Game->game_status === 'RANDOM') {
                continue;
            }

            $Game->opponent = $this->setFacebookInfo($Game->opponent);

            list($turn, $turns) = explode(';', $Game->turns_overview);
            $turns = explode(',', $turns);

            foreach ($turns as &$turns_value) {
                $turns_value = explode('-', $turns_value);
            }

            unset($turns_value);

            $Game->turn = $turn;
            $Game->turns = $turns;

            $this->games['all'][$Game->id] = $Game;

            if (in_array($Game->game_status, array('ACTIVE', 'PENDING_MY_APPROVAL', 'PENDING_FIRST_MOVE'), true)) {
                $this->games['active'][$Game->id] = $Game;

                if ($Game->my_turn) {
                    $this->games['turn'][$Game->id] = $Game;
                } else {
                    $this->games['waiting'][$Game->id] = $Game;
                }
            } else if ($Game->game_status == 'PENDING_FRIENDS_APPROVAL') {
                $this->games['pending'][$Game->id] = $Game;
            } else {
                $this->games['ended'][$Game->id] = $Game;
            }
        }

        $this->Timer->mark('END: Mezclabro->loadGames');

        return $this->games;
    }

    public function getGames ($status = '')
    {
        $this->_loggedOrDie();

        if (empty($this->games)) {
            $this->loadGames();
        }

        return ($status) ? $this->games[$status] : $this->games;
    }

    public function preloadGame ($game)
    {
        $this->_loggedOrDie();

        $this->Timer->mark('INI: Mezclabro->preloadGame');

        if ($this->Cache && $this->Cache->exists('game-'.$game)) {
            $Game = $this->Cache->get('game-'.$game);
        } else {
            $Game = $this->Curl->get('users/'.$this->user.'/games/'.$game);

            if (!is_object($Game) || empty($Game->id)) {
                return false;
            }

            if ($this->Cache && ($Game->game_status === 'ENDED')) {
                $this->Cache->set('game-'.$game, $Game);
            }
        }

        $Game->opponent = $this->setFacebookInfo($Game->opponent);

        if (in_array($Game->game_status, array('ACTIVE', 'PENDING_MY_APPROVAL', 'PENDING_FIRST_MOVE', 'PENDING_FRIENDS_APPROVAL'), true)) {
            $Game->active = true;
        } else {
            $Game->active = false;
        }

        list($turn, $turns) = explode(';', $Game->turns_overview);

        $turns = explode(',', $turns);

        $im = $Game->player_number - 1;

        foreach ($turns as &$turns_value) {
            $turns_value = explode('-', $turns_value);

            $my_points = $turns_value[$im];

            unset($turns_value[$im]);

            $opponent_points = implode('', $turns_value);

            $turns_value = array($my_points, $opponent_points);
        }

        unset($turns_value);

        $Game->turn = intval($turn);
        $Game->turns = $turns;

        $Game->my_turn = isset($Game->my_turn) ? $Game->my_turn : false;

        $this->Game = $Game;

        $this->Timer->mark('END: Mezclabro->preloadGame');

        return $Game;
    }

    public function getGame ($game, $reload = false)
    {
        if (empty($this->Game) || ($this->Game->id != $game) || $reload) {
            $this->preloadGame($game);
        }

        return $this->Game;
    }

    public function getRound ($game, $round)
    {
        if (empty($this->Game) || ($this->Game->id != $game)) {
            $this->preloadGame($game);
        }

        $this->Timer->mark('INI: Mezclabro->getRound');

        $this->setLanguage($this->Game->language);
        $this->loadLanguage();

        $Round = $this->Curl->get('users/'.$this->user.'/games/'.$game.'/rounds/'.$round);

        if (!is_object($Round) || empty($Round->board)) {
            return false;
        }

        $Round->board = $this->getBoardLetters($Round->board);

        $Round->bonus = $this->getBonus($Round->bonus);

        $Round->board_words = $this->getBoardWords($Round->board_words, $Round->board, $Round->bonus);

        $Round->total_words = count($Round->board_words);

        $Round->total_points = 0;

        foreach ($Round->board_words as $word) {
            $Round->total_points += $word['points'];
        }

        if (isset($Round->my_turn)) {
            $Round->my_turn->words = explode(',', $Round->my_turn->words);

            foreach ($Round->my_turn->words as &$turn) {
                list($word, $positions, $points) = explode(':', $turn);

                $turn = array(
                    'word' => $word,
                    'positions' => array(),
                    'points' => $points
                );

                $positions = uni2ords($positions);

                foreach ($positions as $position) {
                    $turn['positions'][$position - 1] = $Round->board[$position - 1];
                }
            }

            unset($turn);

            usort($Round->my_turn->words, function ($a, $b) {
                return $a['points'] > $b['points'] ? -1 : 1;
            });
        }

        $this->Timer->mark('END: Mezclabro->getRound');

        return $this->Round = $Round;
    }

    private function getBoardLetters ($board)
    {
        if (is_string($board)) {
            $board = explode(',', $board);
        }

        array_walk($board, function (&$value) {
            $value = preg_replace('#\:.*#', '', $value);
        });

        return $board;
    }

    private function getBonus ($bonus)
    {
        if (empty($bonus)) {
            return array();
        }

        $bonus = explode(',', $bonus);
        $result = array();

        foreach ($bonus as $value) {
            list($position, $type) = explode('-', $value);

            $result[$position] = $type;
        }

        return $result;
    }

    public function getBoardWords ($words, $board, $bonus = array())
    {
        $this->_loggedOrDie();

        $this->Timer->mark('INI: Mezclabro->getBoardWords');

        if (is_string($words)) {
            $words = explode(',', $words);
        }

        foreach ($words as &$word) {
            $positions = $this->getWordPositions($word, $board);
            $points = $this->getWordPoints($positions, $board, $bonus);

            $word = array(
                'word' => $word,
                'positions' => $positions,
                'points' => $points
            );
        }

        unset($word);

        usort($words, function ($a, $b) {
            return ($a['points'] > $b['points']) ? -1 : 1;
        });

        $this->Timer->mark('END: Mezclabro->getBoardWords');

        return $words;
    }

    private function getWordPositions ($word, $board)
    {
        if (is_string($word)) {
            $word = $this->splitWord($word);
        }

        $positions = array();

        foreach ($word as $letter) {
            $positions[] = array(
                'letter' => $letter,
                'positions' => array_keys($board, $letter)
            );
        }

        $positions = $this->getAllNear($positions);

        $result = array();

        foreach ($word as $key => $letter) {
            $result[$positions[$key]] = $letter;
        }

        return $result;
    }

    private function getAllNear ($letters, $valid = array())
    {
        if (empty($letters)) {
            return $valid;
        }

        $letter = array_shift($letters);

        $positions = $letter['positions'];

        if (count($positions) === 1) {
            $valid[] = $positions[0];
            return $this->getAllNear($letters, $valid);
        }

        $previous = $valid ? end($valid) : null;

        foreach ($positions as $position) {
            if (in_array($position, $valid)) {
                continue;
            }

            if ($previous === null) {
                $valid[] = $position;
                $test = $this->getAllNear($letters, $valid);

                if ($test === false) {
                    array_pop($valid);
                    continue;
                }

                return $test;
            } else {
                $search = array();

                if (($previous - 4) >= 0) {
                    $search[] = $previous - 4;
                } if (($previous + 4) <= 15) {
                    $search[] = $previous + 4;
                } if ((($previous - 1) >= 0) && (($previous % 4) !== 0)) {
                    $search[] = $previous - 1;

                    if (($previous - 5) >= 0) {
                        $search[] = $previous - 5;
                    }

                    if (($previous + 3) <= 15) {
                        $search[] = $previous + 3;
                    }
                } if ((($previous + 1) <= 15) && ((($previous + 1) % 4) !== 0)) {
                    $search[] = $previous + 1;

                    if (($previous - 3) >= 0) {
                        $search[] = $previous - 3;
                    }

                    if (($previous + 5) <= 15) {
                        $search[] = $previous + 5;
                    }
                }

                if (in_array($position, $search)) {
                    $valid[] = $position;
                    $test = $this->getAllNear($letters, $valid);

                    if ($test === false) {
                        array_pop($valid);
                        continue;
                    }

                    return $test;
                }
            }
        }

        return false;
    }

    public function getWordPoints ($word, $board, $bonus = array())
    {
        $this->_loggedOrDie();

        if (!is_array($word)) {
            $word = $this->getWordPositions($word, $board);
        }

        $total = 0;

        foreach ($word as $position => $letter) {
            $points = $this->points[$letter];

            if (isset($bonus[$position])) {
                if ($bonus[$position] === 'DL') {
                    $points *= 2;
                } else if ($bonus[$position] === 'TL') {
                    $points *= 3;
                }
            }

            $total += $points;
        }

        foreach (array_keys($word) as $position) {
            if (isset($bonus[$position]) === false) {
                continue;
            }

            if ($bonus[$position] === 'DW') {
                $total *= 2;
            } else if ($bonus[$position] === 'TW') {
                $total *= 3;
            }
        }

        $len = count($word);

        if ($len <= 3) {
            return $total;
        }

        if ($len === 4) {
            $total += 2;
        } else if ($len === 5) {
            $total += 4;
        } else if ($len === 6) {
            $total += 8;
        } else if ($len === 7) {
            $total += 12;
        } else if ($len === 8) {
            $total += 18;
        } else if ($len === 9) {
            $total += 25;
        } else if ($len === 10) {
            $total += 30;
        } else if ($len === 11) {
            $total += 40;
        } else if ($len === 12) {
            $total += 50;
        }

        return $total;
    }

    public function getBoard ()
    {
        $this->_loggedOrDie();

        $tiles = $this->Round->board;
        $bonus = $this->Round->bonus;

        $board = '<tr>';

        for ($i = 0; $i < (4 * 4); $i++) {
            if (($i > 0) && (($i % 4) === 0)) {
                $board .= '</tr><tr>';
            }

            $tile = $tiles[$i];

            $board .= '<td><div class="tile-50">';
            $board .= '<span class="letter">'.$tile.'</span>';
            $board .= '<span class="points">'.$this->points[$tile].'</span>';

            if (isset($bonus[$i])) {
                $board .= '<span class="bonus bonus-'.$bonus[$i].'">'.$bonus[$i].'</span>';
            }

            $board .= '</div></td>';
        }

        $board .= '</tr>';

        return $board;
    }

    public function getChat ()
    {
        $this->_loggedOrDie();

        $Chat = $this->Curl->get('users/'.$this->user.'/games/'.$this->Game->id.'/chat?all=true');

        if (!is_object($Chat) || empty($Chat->list)) {
            return array();
        }

        usort($Chat->list, function ($a, $b) {
            return (strtotime($a->date) > strtotime($b->date) ? 1 : -1);
        });

        return $Chat->list;
    }

    public function setChat ($message)
    {
        $this->_loggedOrDie();

        $message = trim($message);

        if (empty($message)) {
            return false;
        }

        return $this->Curl->post('users/'.$this->user.'/games/'.$this->Game->id.'/chat', array(
            'message' => $message
        ));
    }

    public function resetChat ()
    {
        $this->_loggedOrDie();

        return $this->Curl->get('users/'.$this->user.'/games/'.$this->Game->id.'/chat?reset=true');
    }

    public function playGame ($post)
    {
        $this->_loggedOrDie();

        if (!isset($post['words']) || empty($post['words'])) {
            return false;
        }

        if (empty($this->Game->active) || empty($this->Game->my_turn)) {
            return false;
        }

        $words = $regular = '';
        $points = 0;

        foreach ($this->Round->board_words as $word) {
            if (!in_array($word['word'], $post['words'])) {
                continue;
            }

            $points += $word['points'];

            $positions = array_map(function ($value) {
                return chr($value + 1);
            }, array_keys($word['positions']));

            $words .= implode('', $positions).':'.$word['points'].',';
        }

        if (isset($this->Round->my_turn->powerups)) {
            $powerups = $this->Round->my_turn->powerups;
        } else {
            $powerups = 'SAVE_NAMEMIX#SAVE_STATEAVAILABLE#SAVE_USAGE_COUNT-1#SAVE_DURATION1000;';
        }

        return $this->Curl->post('users/'.$this->user.'/games/'.$this->Game->id.'/rounds/'.$this->Game->turn, array(
            'words' => mb_substr($words, 0, -1),
            'power_ups' => $powerups,
            'turn_type' => 'PLAY',
            'points' => $points,
            'coins' => 0
        ));
    }

    public function newGame ($language, $user = '')
    {
        $this->_loggedOrDie();

        if (!in_array($language, $this->languages, true)) {
            return false;
        }

        $user = intval($user);
        $data = array('language' => strtoupper($language));

        if ($user && preg_match('/^[0-9]+$/', $user)) {
            $data['opponent'] = array('id' => $user);
        }

        return $this->Curl->post('users/'.$this->user.'/games', $data);
    }

    public function resignGame ()
    {
        $this->_loggedOrDie();

        return $this->turnType('RESIGN');
    }

    public function turnType ($type, $data = null)
    {
        $this->_loggedOrDie();

        if (empty($this->Game->active)) {
            return false;
        }

        $data['type'] = $type;

        return $this->Curl->post('users/'.$this->user.'/games/'.$this->Game->id.'/turns', ($data ?: null));
    }

    private function loadLanguage ()
    {
        $this->loadPoints();
    }

    private function loadPoints ()
    {
        if ($this->points) {
            return true;
        }

        $this->points = array();

        $file = BASE_PATH.'languages/'.$this->language.'/points.php';

        if (!is_file($file)) {
            return false;
        }

        $letters = include ($file);

        foreach ($letters as $letter => $points) {
            $this->points[$letter] = $points;
        }

        uksort($this->points, function($a, $b) {
            return mb_strlen($b) - mb_strlen($a);
        });
    }

    private function splitWord ($word)
    {
        if (!is_string($word)) {
            return $word;
        }

        $words = array();

        foreach (array_keys($this->points) as $letter) {
            $start = 0;

            while (($position = mb_strpos($word, $letter, $start)) !== false) {
                $strlen = mb_strlen($letter);
                $words[$position] = mb_substr($word, $position, $strlen);
                $start = $position + $strlen;
            }
        }

        ksort($words);

        for ($i = 0; isset($words[$i]); $i++) {
            $strlen = mb_strlen($words[$i]);

            if (empty($strlen)) {
                $this->Debug->show($words);
            }

            if (($strlen === 1) || empty($words[$i + 1])) {
                continue;
            }

            if (mb_substr($words[$i], -1) === $words[$i + 1]) {
                unset($words[$i + 1]);

                $words = array_values($words);

                ++$i;
            }
        }

        return $words;
    }
}
