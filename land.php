<?php

require_once "./classes/user.class.php";

$user = new User();
$emails = $user->getQueuedEmails();
if (empty($emails)) {
  header('location: send-email');
} else {
 $xx = $emails;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Admin | Bulk Email Service</title>
    <link rel="stylesheet" type="text/css" href="jquery.dataTables.min.css">
    <link rel="stylesheet" href="bootstrap.min.css">
</head>

<body>
        <div class="container mt-5">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-12 m-auto">
                    <table class="table table-bordered table-hovered table-striped" id="emailTable" style="margin-top: 1rem;">
                        <thead>
                            <th> Reciever Emails </th>
                            <th> Sender Email </th>
                            <th> Sender Name </th>
                            <th> Subject </th>
                            <th> Message </th>
                            <th> Schedule </th>
                            <th> Status </th>
                            <th> Privilege </th>
                        </thead>

                        <tbody>

                            <?php
                            foreach ($xx as $email) : ?>

                                <tr>
                                    <td> <?php echo $email['email_address']; ?> </td>
                                    <td> <?php echo $email['from_email']; ?> </td>
                                    <td> <?php echo $email['name']; ?> </td>
                                    <td> <?php echo $email['subject']; ?> </td>
                                    <td> <?php echo $email['message']; ?> </td>
                                    <td> <?php echo $user->convertTimeFormat($email['schedule_time']); ?> </td>
                                    <td>
                                        <?php
                                        if ($email['is_job'] == 0) {
                                            echo '<a href="" class="btn btn-warning">Pending</a>';
                                        } else {
                                            echo '<a href="" class="btn btn-success">Delivered</a>';
                                        } ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="" class="btn btn-danger">Block</a>'; ?>
                                    </td>
                                </tr>


                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p class="copyright">Built with &#129505; <br> <a href="https://wa.me/2348035630576" style="color: #43A047; text-decoration: none!important;" target="_blank" title="Isaac Ojerumu">Isaac Ojerumu</a></p>
                </div>
            </div>
        </div>

    <script src="jquery.min.js"></script>
    <script src="jquery.dataTables.min.js"></script>
    <script src="bootstrap.min.js"></script>
    <script>
        history.replaceState("", "", "admin");

        $(document).ready(function() {
            $('#emailTable').DataTable();
        });

        function fetchdata() {
            $.ajax({
                url: 'queue.php',
                type: 'post',
                success: function(data) {
                    // Perform operation on return value
                    // alert(data);
                    window.location = 'admin';
                    console.log(data)
                },
                complete: function(data) {
                    setTimeout(fetchdata, 300000); // 300000 milliseconds is 5 minutes
                }
            });
        }

        $(document).ready(function() {
            setTimeout(fetchdata, 300000);
        });
    </script>
</body>

</html>