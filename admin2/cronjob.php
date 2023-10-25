<?php

require_once "./maincore.php";
require_once "./models/route_access.php";

function getUsers()
{
  $sql = "SELECT * FROM arioo_users";
  $result = dbquery($sql);
  $records = array();
  while ($row = dbarray($result)) {
    $records[] = $row;
  }
  return $records;
}

function sendExpiredEmail($to, $username, $days)
{

  $subject = $days <= 0 ? "Time is Running Out: Renew Your Account Today" : 'Reminder: Your Account Will Expire Soon';
  $expiredMessage = "
<p style=\"color: #666666; justify\">
We regret to inform you that your account has expired.
<br>
As a result, you will no longer have access to our services.
<br>
If you wish to continue using our services, please renew your account as soon as possible.
<br>
Thank you for your understanding.
<br>
Best regards,
<br>
Soshianest Team
<p style=\"color: #666666;\">
<a href=\"soshianest.ai\">soshianest.ai</a>
</p>
</p>";
  $remainingMessage = "
<p style=\"color: #666666; justify\">
We would like to remind you that your account with us will expire in just $days days.
Please note that once your account has expired, you will no longer have access to our services.
To continue using our services, we kindly ask you to renew your account as soon as possible.
Thank you for your attention to this matter and we hope to continue serving you soon.
Best regards,
Soshianest Team
<p style=\"color: #666666;\">
<a href=\"soshianest.ai\">soshianest.ai</a>
</p>
</p>";





  $msg = $days <= 0 ? $expiredMessage : $remainingMessage;

  $message = "<!DOCTYPE html>
<html>
  <head>
    <title>My Email Template</title>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
  </head>
  <body style=\"background-color: #f5f5f5; font-family: Arial, sans-serif; padding: 20px;\">
    <table style=\"background-color: #ffffff; border-collapse: collapse; border: 1px solid #cccccc; width: 100%;\">
      <tr>
        <td style=\"padding: 20px;\">
        <h1 style=\"color: #333333;\">Dear $username,</h1>
        $msg
        </td>
      </tr>
    </table>
  </body>
</html>";


  $message = wordwrap($message, 80);
  $headers = 'From: support@soshianest.ai' . "\r\n" .
    'Content-type: text/html; charset=iso-8859-1' . "\r\n" .
    'X-Mailer: PHP/' . phpversion();

  return mail($to, $subject, $message, $headers);

}



$users = getUsers();

foreach ($users as $user) {
  $access = getRouteAccessByUserId($user["user_id"]);
  if ($access == null)
    continue;
  $email = $user["user_email"];
  $username = $user["user_name"];
  $expire_time = $access["expire_time"];
  $current_time = time();
  $time_diff = $expire_time - $current_time;
  $remaining_days = floor($time_diff / (60 * 60 * 24));
  echo "<br>";
  echo "email: $email";
  echo "<br>";
  echo "expire_time: $expire_time";
  echo "<br>";
  echo "current_time: $current_time";
  echo "<br>";
  echo "time_diff: $time_diff";
  echo "<br>";
  echo "remaining_days: $remaining_days";
  echo "<br><br><br><br><br><br><br><br><br>";


  if ($remaining_days == 1 || $remaining_days == 3 || $remaining_days === 7 || $remaining_days === 0) {
    // sendExpiredEmail($email, $username, $remaining_days)
  }



}

?>