<?php

/**
 * @param array $user
 * @return bool
 */
function mail_user_delete($user)
{
    return engelsystem_email_to_user(
        $user,
        '[engelsystem] ' . _('Your account has been deleted'),
        _('Your angelsystem account has been deleted. If you have any questions regarding your account deletion, please contact heaven.')
    );
}
