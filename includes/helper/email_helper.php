<?php

function engelsystem_email_to_user($recipient_user, $title, $message, $not_if_its_me = false) {
  global $user;

  if ($not_if_its_me && $user['UID'] == $recipient_user['UID'])
    return true;

  gettext_locale($recipient_user['Sprache']);

  $message = sprintf(_("Hi %s,"), $recipient_user['Nick']) . "\r\n\r\n" . _("here is a message for you from the engelsystem:") . "\r\n\r\n" . $message . "\r\n\r\n" . _("You are receiving this email because you are registered as a volunteer for Mozilla Festival 2016.");

  gettext_locale();
  return engelsystem_email($recipient_user['email'], $title, $message);
}

function engelsystem_email($address, $title, $message) {
  //return mail($address, $title, $message, "Content-Type: text/plain; charset=UTF-8\r\r\nFrom: Engelsystem <noreply@engelsystem.de>");

  $mail = new PHPMailer;
  $mail->isSMTP();
  $mail->Host = getenv('SMTP_HOST');
  $mail->SMTPAuth = true;
  $mail->Username = getenv('SMTP_USERNAME');
  $mail->Password = getenv('SMTP_PASSWORD');
  $mail->SMTPSecure = 'tls';
  $mail->Port = getenv('SMTP_PORT');
  $mail->setFrom(getenv('SMTP_FROM_ADDRESS'), getenv('SMTP_FROM_NAME'));
  $mail->addAddress($address, '');
  $mail->addReplyTo(getenv('SMTP_REPLYTO_ADDRESS'), '');
  $mail->ContentType = 'text/plain';
  $mail->IsHTML(false);
  $mail->Subject = $title;
  $mail->Body    = $message;
  return $mail->send();
}

?>
