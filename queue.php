<?php

require_once "./classes/user.class.php";

$user = new User();

// a query that checks if is_job and only send mails within the range of current time and next five minutes (script runs every five minutes)
// send time range to query by
$timeInterval = date('Y-m-d H:i:s', strtotime('+ 5 minutes'));

$user->runQueue($timeInterval);