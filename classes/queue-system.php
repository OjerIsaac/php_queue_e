<?php

require_once "db.config.php";
require_once 'env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

class Queue
{
    protected $db;
    private $mail;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->db = $this->db->connect();
        $this->mail = new PHPMailer(true);
        $this->mail->isSMTP();
        $this->mail->Host = 'lunikdata.com';
        $this->mail->SMTPAuth = true;
        $this->mail->Username = $_ENV['USERNAME'];
        $this->mail->Password = $_ENV['PASSWORD'];
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = $_ENV['PORT'];
    }


    /**
     * Define a function to add a new email to the queue
     */
    public function add_email_to_queue(string $to, string $subject, string $message): bool
    {
        $query = "INSERT INTO email_queue (to_address, subject, message) VALUES (?, ?, ?)')";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$to, $subject, $message]);
        return true;
    }

    /**
     * Define a function to get the next email from the queue
     */
    public function get_next_email_from_queue(): PDOStatement
    {
        $query = 'SELECT * FROM email_queue WHERE status = "queued" ORDER BY created_at ASC LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Define a function to mark an email as dispatched
     */
    public function mark_email_as_dispatched(string $id): PDOStatement
    {
        $query = 'UPDATE email_queue SET status = "dispatched", dispatched_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    /**
     * Define a function to mark an email as failed
     */
    public function mark_email_as_failed(string $id): PDOStatement
    {
        $query = 'UPDATE email_queue SET status = "failed", failed_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    /**
     * Define a function to send email using PHPMailer
     */
    public function send_email(string $emailTo, string $emailSubject, string $emailMessage)
    {
        try {
            $this->mail->setFrom('info@lunikdata.com', 'Ticket Support');
            $this->mail->addAddress($emailTo);
            $this->mail->isHTML(true);
            $this->mail->Subject = $emailSubject;
            $this->mail->Body = $emailMessage;

            $this->mail->send();
        } catch (Exception $e) {
            // Log the error or handle it in some other way
            return false;
        }

        return true;
    }

    /**
     * Main loop to process the queue
     */
    public function process_email_queue(): void
    {
        while ($email = $this->get_next_email_from_queue()) {
            // Send the email
            $sent_successfully = $this->send_email($email['to_address'], $email['subject'], $email['message']);

            // Update the database based on whether the email was sent successfully
            if ($sent_successfully) {
                $this->mark_email_as_dispatched($email['id']);
            } else {
                $this->mark_email_as_failed($email['id']);

                // If the email failed to send, add it back to the queue
                // to try again later
                $failed_email = $this->get_next_email_from_queue();
                if ($failed_email) {
                    $this->add_email_to_queue($failed_email['to_address'], $failed_email['subject'], $failed_email['message']);
                }
            }
        }
    }
}
