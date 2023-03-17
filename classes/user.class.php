<?php

require_once "db.config.php";
require_once 'env.class.php';
$__DotEnvironment = new DotEnvironment(realpath("./.env"));

//reset the timezone default
date_default_timezone_set('Africa/Lagos');

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

class User
{

  private $mail;
  protected $db;

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
   * @return string
   */
  public function generate_uuid()
  {
    $uuid = sprintf(
      '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0x0fff) | 0x4000,
      mt_rand(0, 0x3fff) | 0x8000,
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff),
      mt_rand(0, 0xffff)
    );
    return $uuid;
  }

  /**
   * @param mixed $emails
   * @param mixed $from_email
   * @param mixed $name
   * @param mixed $message
   * @param mixed $subject
   * @param mixed $time
   * @param mixed $code
   * @return true|void
   */
  public function addEmail($emails, $from_email, $name, $message, $subject, $time, $code)
  {
    foreach ($emails as $email) {
      $null = 0;
      $sql = "INSERT INTO emails (email_address, from_email, name, message, subject, unique_ids, schedule_time, is_job, date)" . "VALUES (?,?,?,?,?,?,?,?,?)";
      $stmt = $this->db->prepare($sql);
      $stmt->execute([$email, $from_email, $name, $message, $subject, $code, $time, $null, date('Y-m-d H:i:s')]);

      return true;
    }
  }

  /**
   * @param mixed $code
   * @return bool
   */
  public function sendEmail($code)
  {
    $rows = $this->getEmails($code)->fetch(PDO::FETCH_ASSOC);
    $emails = explode(",", $rows['email_address']);
    // print_r($emails); exit;

    // Loop through the emails and send the email to each recipient
    foreach ($emails as $emailAddress) {
      try {
        $this->mail->setFrom($rows['from_email'], $rows['name']);
        $this->mail->addAddress($emailAddress);
        $this->mail->isHTML(true);
        $this->mail->Subject = $rows['subject'];
        $this->mail->Body = $rows['message'];

        $this->mail->send();
      } catch (Exception $e) {
        // Log the error or handle it in some other way
        return false;
      }
    }

    return true;
  }

  /**
   * @param mixed $code
   * @return \PDOStatement|false|void
   */
  public function getEmails($code)
  {
    $sql = "SELECT * from emails WHERE unique_ids = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$code]);

    $count_row = $stmt->rowCount();

    if ($count_row == 1) {
      return $stmt;
    }
  }

  /**
   * @param mixed $code
   * @return true
   */
  public function updateJob($code)
  {
    $query = "UPDATE emails SET is_job = ? WHERE unique_ids = ?";
    $stmt = $this->db->prepare($query);
    $values = array('1', $code);
    $stmt->execute($values);

    return true;
  }

  /**
   * @return \PDOStatement|false|void
   */
  public function getQueuedEmails()
  {
    $sql = "SELECT * from emails";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  /**
   * @param mixed $x
   * @return string
   */
  public function getFutureTime($x)
  {
    $future_time = date('Y-m-d H:i:s', strtotime('+' . $x . ' minutes'));
    return $future_time;
  }

  /**
   * @param mixed $timeInterval
   * @return \PDOStatement|false|void
   */
  public function getEmailOnQueue($timeInterval)
  {
    $stat = 0;
    $sql = "SELECT * from emails WHERE is_job = ? AND schedule_time <= ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$stat, $timeInterval]);

    $count_row = $stmt->rowCount();

    if ($count_row > 0) {
      return $stmt;
    }
  }

  /**
   * @param mixed $timeInterval
   * @return bool
   */
  public function runQueue($timeInterval)
  {
    $rows = $this->getEmailOnQueue($timeInterval)->fetch(PDO::FETCH_ASSOC);
    $emails = explode(",", $rows['email_address']);
    // print_r($emails); exit;
    $uuid = $rows['unique_ids'];
    $this->updateJob($uuid);

    // Loop through the emails and send the email to each recipient
    foreach ($emails as $emailAddress) {
      try {
        $this->mail->setFrom($rows['from_email'], $rows['name']);
        $this->mail->addAddress($emailAddress);
        $this->mail->isHTML(true);
        $this->mail->Subject = $rows['subject'];
        $this->mail->Body = $rows['message'];

        $this->mail->send();
      } catch (Exception $e) {
        // Log the error or handle it in some other way
        return false;
      }
    }
    
    return true;
  }

  /**
   * @param mixed $timeStr
   * @return string
   */
  public function convertTimeFormat($timeStr)
  {
    $time = strtotime($timeStr);
    $formattedTime = date("g:ia D M Y", $time);
    return $formattedTime;
  }
}
