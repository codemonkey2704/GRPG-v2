<?php
declare(strict_types=1);
require __DIR__.'/inc/nliheader.php';
if (array_key_exists('contact', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['emailer']) || empty($_POST['subject']) || empty($_POST['message'])) {
        echo Message("You missed a reqired input please go back and correct it. <a href='contact.php'>[Go Back]</a>", 'Error', true);
    }
    $sender = array_key_exists('emailer', $_POST) && filter_var($_POST['emailer'], FILTER_VALIDATE_EMAIL) ? $_POST['emailer'] : null;
    $subject = array_key_exists('subject', $_POST) && is_string($_POST['subject']) ? strip_tags($_POST['subject']) : false;
    if (strlen($subject) > 75 || strlen($subject) < 5) {
        echo Message("You'r subject must be between 5 and 75 characters long.", 'Error', true);
    }
    $message = array_key_exists('message', $_POST) && is_string($_POST['message']) ? strip_tags($_POST['message']) : false;
    if (strlen($message) > 500 || strlen($message) < 10) {
        echo Message("You'r message must be between 10 and 500 characters you used ".strlen($message)." characters.. <a href='contact.php'>[Go Back]</a>", 'Error', true);
    }
    $message = wordwrap($message, 70);
    $headers = 'From: '.$sender.''."\r\n".'CC: no-reply@thegrpg-project.com';
    $sendto = 'support@thegrpg-project.com';
    if (!mail($sendto, $subject, $message, $headers)) {
        echo Message("Couldn't send that email. <a href='contact.php'>[Go Back]</a>", 'Error', true);
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $db->query('INSERT INTO contactmessages (email, subject, message) VALUES (?, ?, ?)');
        $db->execute([$sender, $subject, $message]);
        mail($sendto, $subject, $message, $headers);
        echo Message("Your email has been sent to the main owner. He will reply shortly! <a href='index.php'>Login</a>");
    }
} ?>
<tr>
    <th class="content-head">Contact Form</th>
</tr>
<tr>
    <td class="content">
        <form action="contact.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <table width="85%" cellspacing="0" cellspading="1">
                <tr>
                    <td>
                        <div class="pure-control-group">
                            <label for="username">Email:</label>
                            <input type="email" name="emailer" id="username" size="22" required autofocus />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="pure-control-group">
                            <label for="username">Subject:</label>
                            <input type="text" name="subject" id="username" size="22" required />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="pure-control-group">
                            <label for="username">Your Message:</label>
                            <textarea class="textarea" name="message" rows="5" cols="15">

                        </textarea>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="pure-controls">
                            <button type="submit" name="contact" class="pure-button pure-button-primary">Send Email to Game Owner</button>
                        </div>
                    </td>
                </tr>
            </table>
        </form>
    </td>
</tr>
<?php
include __DIR__.'/inc/nlifooter.php';
