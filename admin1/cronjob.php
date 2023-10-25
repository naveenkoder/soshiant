<?php

$to = 'omid.rmi@gmail.com';


$subject = 'the subject';
$message = `<!DOCTYPE html>
<html>
  <head>
    <title>My Email Template</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  </head>
  <body style="background-color: #f5f5f5; font-family: Arial, sans-serif; padding: 20px;">
    <pre>
      Dear [User],
      We hope this message finds you well. We wanted to inform you that your account on our website has expired as of [expiration date]. We value your patronage and would like to remind you that you can renew your account at any time by visiting our website and following the renewal instructions.
      In the meantime, we wanted to let you know that we will be sending you an email to confirm the expiration of your account. If you have any questions or concerns, please don't hesitate to reach out to our customer support team, who will be happy to assist you.
      Thank you for your continued support and we hope to see you back on our website soon.
      Best regards
    </pre>
  </body>
</html>`;
$headers = 'From: admin@arioo.ir' . "\r\n" .
  'Content-type: text/html';

$result = mail($to, $subject, $message, $headers);



if ($result) {
  echo "Email sent successfully.";
} else {
  echo "Email sending failed.";
}

?>