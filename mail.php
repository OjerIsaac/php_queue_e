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
            <select name="timeSent" id="timeSent">
                <option value="" selected disabled>--------</option>
                <option value="5mins">5mins later</option>
                <option value="10mins">10mins later</option>
                <option value="30mins">30mins later</option>
                <option value="1hr">1hour later</option>
            </select>

            <button type="submit" name="submit" style="margin-top: 2rem;">Send Email</button>

            <p class="copyright">Built by <a href="https://wa.me/2348035630576" style="color: #43A047" target="_blank" title="Isaac Ojerumu">Isaac Ojerumu</a></p>
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