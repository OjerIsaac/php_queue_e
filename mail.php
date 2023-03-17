<?php

// Load DotEnvironment Class
require_once('./classes/env.class.php');
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

require_once "./classes/user.class.php";

$user = new User();

if (isset($_POST['submit'])) {
    $required_fields = array('emails', 'senderEmail', 'message', 'senderName', 'subject', 'timeSent');
    $empty_fields = array();

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $empty_fields[] = $field;
        }
    }

    if (empty($empty_fields)) {
        switch ($_POST['timeSent']) {
            case 'now':
                $time = 1;
                $timer = date('Y-m-d H:i:s');
                break;
            case '5mins':
                $timer = '5mins';
                $time = $user->getFutureTime(5);
                break;
            case '10mins':
                $time = $user->getFutureTime(10);
                $timer = '10mins';
                break;
            case '30mins':
                $time = $user->getFutureTime(30);
                $timer = '30mins';
                break;
            case '1hr':
                $time = $user->getFutureTime(60);
                $timer = '1hr';
                break;
            default:
                $time = date('Y-m-d H:i:s');
                break;
        }
        if ($time == 1) {
            // generate unique id
            $code = $user->generate_uuid();
            // add to queue
            $email_job = $user->addEmail($_POST['emails'], $_POST['senderEmail'], $_POST['senderName'], $_POST['message'], $_POST['subject'], $timer, $code);
            if ($email_job) {
                // send mails
                $send_email = $user->sendEmail($code);
                if ($send_email) {
                    $message = "<div style='width: fit-content; margin: 1.2rem auto; color: 43A047;'>Emails sent successfully!</div>";

                    echo "<script type='text/javascript'>";
                    echo "setTimeout(function() {
                        window.location.href = 'send-email';";
                    echo "}, 3500);</script>";
                    // update job
                    $user->updateJob($code);
                } else {
                    //TODO: Add to queue
                    $message = '<div style="width: fit-content; margin: 1.2rem auto; color: red;">Something went wrong, emails could not be sent. We would try again in 30secs</div>';
                    echo "<script type='text/javascript'>";
                    echo "setTimeout(function() {
                        window.location.href = 'send-email';";
                    echo "}, 3500);</script>";
                }
            } else {
                //TODO: Add to queue
                $message = '<div style="width: fit-content; margin: 1.2rem auto; color: red;">Something went wrong, emails could not be uploaded. We would try again in 30secs</div>';
                echo "<script type='text/javascript'>";
                echo "setTimeout(function() {
                        window.location.href = 'send-email';";
                echo "}, 3500);</script>";
            }
        } else {
            // generate unique id
            $code = $user->generate_uuid();
            // add to queue
            $email_job = $user->addEmail($_POST['emails'], $_POST['senderEmail'], $_POST['senderName'], $_POST['message'], $_POST['subject'], $time, $code);
            $message = "<div style='width: fit-content; margin: 1.2rem auto; color: 43A047;'>Emails would be sent in $timer</div>";
        }
    } else {
        $message = '<div style="width: fit-content; margin: 1.2rem auto; color: red;">This fields ' . implode(', ', $empty_fields) . ' cannot be empty</div>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Bulk Email Service</title>
</head>

<body>
    <div id="loader" style="display: none;">
        <img src="loader.gif" alt="Loading..." />
    </div>
    <div class="container">
        <form id="contact" action="" method="post">
            <h3>Bulk email sending service</h3>
            <h4>Schedule your emails</h4>
            <!-- Error -->
            <?php if (isset($message)) echo $message; ?>
            <!-- /Error -->
            <label for="senderName">Organization name:</label>
            <input type="text" name="senderName" required>

            <label for="senderEmail">Email From:</label>
            <input type="email" name="senderEmail" required>

            <label for="emails">Recepients Email Addresses: (seperate mails with a comma)</label>
            <input type="email" name="emails[]" id="emails" multiple required>

            <label for="subject">Email Subject:</label>
            <input type="text" name="subject" required>

            <label for="message">Email Message:</label>
            <textarea placeholder="Type your message here...." name="message" tabindex="5" required></textarea>

            <label for="timeSent">When you want it sent?</label>
            <select name="timeSent" id="timeSent" required>
                <option value="" selected disabled>--------</option>
                <option value="now">Now</option>
                <option value="5mins">5mins later</option>
                <option value="10mins">10mins later</option>
                <option value="30mins">30mins later</option>
                <option value="1hr">1hour later</option>
            </select>

            <button type="submit" name="submit" style="margin-top: 2rem;">Send Email</button>

            <p class="copyright">Built with &#129505; <br> <a href="https://wa.me/2348035630576" style="color: #43A047; text-decoration: none!important;" target="_blank" title="Isaac Ojerumu">Isaac Ojerumu</a></p>
        </form>
    </div>

    <script src="jquery.min.js"></script>
    <script>
        history.replaceState("", "", "send-email");

        $(document).ready(function() {
            // Listen for form submit event
            $('#contact').submit(function() {
                // Show loader image
                $('#loader').show();
            });
            // Hide loader image
            $('#loader').hide();
        });
    </script>
</body>

</html>