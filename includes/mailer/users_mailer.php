<?php

/**
 * @param User $user_source
 */
function mail_user_delete($user) {
  engelsystem_email_to_user($user, '[engelsystem] ' . _("Your account has been deleted"), _("Your angelsystem account has been deleted. If you have any questions regarding your account deletion, please contact heaven."));
}
?>