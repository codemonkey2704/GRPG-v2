<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
/*
@(#)File:           blackjack.php
@(#)Version:        1.0
@(#)Release:        Date: 3/26/2015
@(#)Last changed:   Date: ??/??/2014
@(#)Purpose:        Blackjack prototype
@(#)Author:         William Hughes
@(#)URL:             www.GameMakersForums.com
@(#)Copyright:      If generate money from this script somehow, a donation would be nice.
@(#)Product:        :PHP Blackjack Prototype
*/
/* Black Jack Points
 2 - 10 = There relative Numbers
 J = 10 Points
 Q = 10 Points
 K = 10 Points
 A = 1 or 11 Points
*/
$count = 1;
if (isset($_POST['hit']) && $_SESSION['bj_state'] === 'in_game') {
    hit('player');
    //check if player busted
    if (countCards($_SESSION['bj_player_cards']) > 21) {
        echo 'You busted, dealer won...';
        $_SESSION['bj_state'] = 2;
    }
}
//player decided to stay
elseif ((isset($_POST['stay'])) && ($_SESSION['bj_state'] === 'in_game')) {
    //calc player & dealer points
    $dealer = countCards($_SESSION['bj_dealer_cards']);
    $player = countCards($_SESSION['bj_player_cards']);
    //if dealer under 17 points, keep hitting.
    while ($dealer < 17) {
        hit('dealer');
        $dealer = countCards($_SESSION['bj_dealer_cards']);
    }
    //dealer has blackjack
    if ($dealer == 21) {
        echo 'Dealer Got BlackJack... You lost...';
    } elseif ($dealer >= $player) { //dealer has more points then player
        //if dealer under 21, they win
        if ($dealer < 21) {
            echo 'Dealer Won...';
        } else { //dealer busted (OVER 21)
            echo 'Congratulations, you won. The dealer busted...';
        }
    } elseif ($player == 21) {
        //player won
        //player has blackjack
        echo 'You got black jack...';
    } else {
        //player one by point values
        echo 'Congratulations, You won...';
    }
    $_SESSION['bj_state'] = 'game_over';
} elseif (isset($_POST['new_game'])) { //create new game
    makeGame();
} elseif ($_SESSION['bj_state'] !== 'in_game') { //usually "first" visit. Create game.
    makeGame();
}
//hit
function hit($who)
{
    $bjCardCount = $_SESSION['bj_card_count'];
    $temp = (array)explode('-', $_SESSION['bj_deck'][$bjCardCount]);
    $cardCount = count($_SESSION['bj_'.$who.'_cards']);
    $suitCount = count($_SESSION['bj_'.$who.'_suits']);
    $_SESSION['bj_'.$who.'_cards'][$cardCount] = $temp[0];
    $_SESSION['bj_'.$who.'_suits'][$suitCount] = $temp[1];
    ++$_SESSION['bj_card_count'];
}
//count value of cards
function countCards($cards)
{
    //reassign some cards by number.
    $count = 0;
    $delay = [];
    for ($x = 0, $xMax = count($cards); $x < $xMax; ++$x) {
        switch ($cards[$x]) {
            case 'k':
            case 'q':
            case 'j':
                $cards[$x] = 10;
            break;
        }
    }
    //start counting the cards
    for ($x = 0, $xMax = count($cards); $x < $xMax; ++$x) {
        //if the card isn't an ACE
        if (is_numeric($cards[$x])) {
            $count += $cards[$x];
        } else { //if card is an ace, we'll count last.
            $delay[] = $cards[$x];
        }
    }
    //check if there's any ACES
    if (count($delay) > 0) {
        //if ONE ACE
        if (count($delay) == 1) {
            //if total count of cards is 10 or less then 10, we'll make ACE 11,
            if ($count <= 10) {
                $count += 11;
            } //if the total count of cards is 21, player busted.
            elseif ($count >= 21) {
                ++$count;
            }
        } else {
            //if more then one ace
            //loop through all aces
            for ($x = 0, $xMax = count($delay); $x < $xMax; ++$x) {
                //if total count is less then 10 minus the count of the other aces, ACE is 11
                if ($count <= 10 - count($delay)) {
                    $count += 11;
                } elseif ($count >= 21) {
                    ++$count;
                }
            }
        }
    }
    //return card count
    return $count;
}
function makeGame()
{
    unset($_SESSION['bj_state'], $_SESSION['bj_deck'], $_SESSION['bj_player_cards'], $_SESSION['bj_dealer_cards'], $_SESSION['bj_dealer_cards'], $_SESSION['bj_dealer_suits'], $_SESSION['bj_card_count'], $_SESSION['bj_player']);
    //set game stat
    $_SESSION['bj_state'] = 'in_game';
    //create deck
    $_SESSION['bj_deck'] = ['2-c', '3-c', '4-c', '5-c', '6-c', '7-c', '8-c', '9-c', '10-c', 'j-c', 'q-c', 'k-c', 'a-c', '2-d', '3-d', '4-d', '5-d', '6-d', '7-d', '8-d', '9-d', '10-d', 'j-d', 'q-d', 'k-d', 'a-d', '2-h', '3-h', '4-h', '5-h', '6-h', '7-h', '8-h', '9-h', '10-h', 'j-h', 'q-h', 'k-h', 'a-h', '2-s', '3-s', '4-s', '5-s', '6-s', '7-s', '8-s', '9-s', '10-s', 'j-s', 'q-s', 'k-s', 'a-s'];
    //shuffle deck
    shuffle($_SESSION['bj_deck']);
    //temp counter
    $count = 0;
    //deal player cards
    for ($x = 0; $x < 2; ++$x) {
        $_SESSION['bj_player'][] = $_SESSION['bj_deck'][$count];
        $_SESSION['bj_dealer'][] = $_SESSION['bj_deck'][($count + 1)];
        $count += 2;
    }
    //display players cards
    for ($x = 0; $x <= 1; ++$x) {
        $temp = (array)explode('-', $_SESSION['bj_player'][$x]);
        $_SESSION['bj_player_cards'][] = $temp[0];
        $_SESSION['bj_player_suits'][] = $temp[1];
    }
    //deal dealers card
    for ($x = 0; $x <= 1; ++$x) {
        $temp = (array)explode('-', $_SESSION['bj_dealer'][$x]);
        $_SESSION['bj_dealer_cards'][] = $temp[0];
        $_SESSION['bj_dealer_suits'][] = $temp[1];
    }
    //check if dealer was dealt blackjack
    if (countCards($_SESSION['bj_dealer_cards']) == 21) {
        echo 'Dealer Won. He was dealt blackjack...';
    } else {
        $_SESSION['bj_card_count'] = 4;
    }
}
?><tr>
    <th class="content-head">Blackjack</th>
    </tr>
<tr>
    <td class="content">
        <div id="outcome" style="color:red"></div>
        <form name="form" method="post" action="">
        <table>
            <tr>
                <td>Dealer's Cards</td>
                <?php
if ($_SESSION['bj_state'] === 'game_over') {
    for ($x = 0; $x < 5; ++$x) {
        ?><td><img src="images/cards/e/<?php echo isset($_SESSION['bj_dealer_cards'][$x]) ? $_SESSION['bj_dealer_cards'][$x].$_SESSION['bj_dealer_suits'][$x] : ''; ?>.jpg" id="dealercard_<?php echo $x + 1; ?>"><br></td><?php
    }
} else {
    for ($x = 0; $x < 5; ++$x) {
        ?><td><img src="images/cards/e/.jpg" name="dealercard_<?php echo $x + 1; ?>" id="dealercard_<?php echo $x + 1; ?>"><br></td><?php
    }
}
?>
            </tr>
            <tr>
                <td>Player's Cards</td>
                <?php
for ($x = 0; $x < 5; ++$x) {
    ?><td>
                        <img src="images/cards/e/<?php echo isset($_SESSION['bj_player_cards'][$x]) ? $_SESSION['bj_player_cards'][$x].$_SESSION['bj_player_suits'][$x] : ''; ?>.jpg" name="playercard_<?php echo $x + 1; ?>" id="playercard_<?php echo $x + 1; ?>">
                    </td><?php
}
?>
            </tr>
        </form>
        <?php
//staying
if (!isset($_POST['stay'])) {
    ?>
            <tr>
                <td colspan="2">
                <!-- JQuery  -->
                <div id="test">
                    <input type="submit" name="hit" id="hit" value="Hit Me" />
                </div>
                <!-- End of jquery -->
                </td>
            </tr>
            <tr>
                <td colspan="2">
                <!-- JQuery  -->
                <div id="test3">
                    <input type="submit" name="stay" id="stay" value="Stay" />
                </div>
                <!-- End of jquery -->
                </td>
            </tr><?php
}
?><tr>
                <td colspan="2">
                <!-- JQuery  -->
                <div id="test1">
                    <input type="submit" name="new_game" id="new_game" value="New Game" />
                </div>
                <!-- End of jquery -->
                </td>
            </tr>
        </table>
    </td>
</tr>
