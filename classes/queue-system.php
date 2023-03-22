<?php

require_once "db.config.php";
require_once 'env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

class Queue 
{
    protected $db;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->db = $this->db->connect();
    }


    /**
     * Define a function to add a new email to the queue
     * @param mixed $to
     * @param mixed $subject
     * @param mixed $message
     * @return void
     */
    public function add_email_to_queue($to, $subject, $message) {
        $query = "INSERT INTO email_queue (to_address, subject, message) VALUES (?, ?, ?)')";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$to, $subject, $message]);
        return true;
    }

    /**
     * Define a function to get the next email from the queue
     * @return $mixed
     */
    public function get_next_email_from_queue() {
        $query = 'SELECT * FROM email_queue WHERE status = "queued" ORDER BY created_at ASC LIMIT 1';
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * Define a function to mark an email as dispatched
     * @param mixed $id
     * @return \PDOStatement|false
     */
    public function mark_email_as_dispatched($id) {
        $query = 'UPDATE email_queue SET status = "dispatched", dispatched_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    /**
     * Define a function to mark an email as failed
     * @param mixed $id
     * @return void
     */
    public function mark_email_as_failed($id) {
        $query = 'UPDATE email_queue SET status = "failed", failed_at = NOW() WHERE id = ?';
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        return $stmt;
    }

    /**
     * Define a function to send email using PHPMailer
     * 
     */
    public function send_email($emailTo, $emailSubject, $emailMessage)
    {
        # code...
    }

    /**
     * Main loop to process the queue
     * @return void
     */
    public function process_email_queue() {
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
