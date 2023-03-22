<?php

require_once 'env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

// Set up a database connection
$db = new PDO('mysql:host="'.$_ENV['DB_HOST'].'";dbname="'.$_ENV['DB_NAME'].'"', '"'.$_ENV['DB_USER'].'"', '"'.$_ENV['DB_PASSWORD'].'"');

// Define a function to add a new email to the queue
function add_email_to_queue($to, $subject, $message) {
    global $db;
    $stmt = $db->prepare('INSERT INTO email_queue (to_address, subject, message) VALUES (?, ?, ?)');
    $stmt->execute([$to, $subject, $message]);
}

// Define a function to get the next email from the queue
function get_next_email_from_queue() {
    global $db;
    $stmt = $db->query('SELECT * FROM email_queue WHERE status = "queued" ORDER BY created_at ASC LIMIT 1');
    return $stmt->fetch();
}

// Define a function to mark an email as dispatched
function mark_email_as_dispatched($id) {
    global $db;
    $stmt = $db->prepare('UPDATE email_queue SET status = "dispatched", dispatched_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
}

// Define a function to mark an email as failed
function mark_email_as_failed($id) {
    global $db;
    $stmt = $db->prepare('UPDATE email_queue SET status = "failed", failed_at = NOW() WHERE id = ?');
    $stmt->execute([$id]);
}

// Define a function to send email using PHPMailer
function send_email($emailTo, $emailSubject, $emailMessage)
{
    # code...
}

// Main loop to process the queue
while ($email = get_next_email_from_queue()) {
    // Send the email
    $sent_successfully = send_email($email['to_address'], $email['subject'], $email['message']);
    
    // Update the database based on whether the email was sent successfully
    if ($sent_successfully) {
        mark_email_as_dispatched($email['id']);
    } else {
        mark_email_as_failed($email['id']);

        // If the email failed to send, add it back to the queue
        // to try again later
        $failed_email = get_next_email_from_queue();
        if ($failed_email) {
            add_email_to_queue($failed_email['to_address'], $failed_email['subject'], $failed_email['message']);
        }
    }
}
