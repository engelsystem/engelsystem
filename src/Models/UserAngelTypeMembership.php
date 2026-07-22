<?php

declare(strict_types=1);

namespace Engelsystem\Models;

enum UserAngelTypeMembership: string
{
    /**
     * Angel type is restricted. User requested membership but is unconfirmed.
     */
    case UNCONFIRMED = 'UNCONFIRMED';

    /**
     * User is a confirmed member. If angel type is not restricted user is a member.
     */
    case MEMBER = 'MEMBER';

    /**
     * User is supporter of the angel type.
     */
    case SUPPORTER = 'SUPPORTER';
}
