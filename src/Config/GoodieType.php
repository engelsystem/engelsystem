<?php

declare(strict_types=1);

namespace Engelsystem\Config;

enum GoodieType : string
{
    case None = 'none';
    case Goodie = 'goodie';
    case Tshirt = 'tshirt';
}
