# DEMONSTRATING PHP QUEUE IN EMAIL SERVICE
This is a bulk email sending service that sends email at a particular time set by the user powered by PHP

## Requirements
- [PHP](https://www.php.net/downloads.php) version 7 or higher
- [MySQL](https://dev.mysql.com/doc/) The world's most popular open source database
- Web hosting eg: [Hostinger](https://www.hostinger.com/web-hosting), [Namecheap](https://www.namecheap.com/hosting/), [Domainking](https://clients.domainking.ng/store/web-hosting) or [XAMPP Server](https://www.apachefriends.org/download.html)

## Note
### Would we need a cron job to get the entire thing working?
- The code here does not require a cron job to run, as it contains a main loop that continuously processes the email queue until it is empty.

- A cron job can be setup on my setup on my server to run at 5minutes intervals
- `*/5 * * * * /usr/bin/php /path/to/queue-system.php >/dev/null 2>&1`
