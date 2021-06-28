<?php

namespace Engelsystem\Test\Unit;

use Faker\Provider\Base;

class FakerProvider extends Base
{
    /** @var string[] */
    protected static $pronouns = ['fae', 'ae', 'e', 'ey', 'he', 'per', 'she', 'tey', 'they', 've', 'xe', 'ze', 'zie'];

    /** @var string[] */
    protected static $shirtSizes = ['S', 'S-G', 'M', 'M-G', 'L', 'L-G', 'XL', 'XL-G', '2XL', '3XL', '4XL'];

    /**
     * @return string
     */
    public function pronoun()
    {
        return static::randomElement(static::$pronouns);
    }

    /**
     * @return string
     */
    public function shirtSize(): string
    {
        return static::randomElement(static::$shirtSizes);
    }
}
