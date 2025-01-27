<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Fixtures;

/**
 * @author Ninos Ego <me@ninosego.de>
 */
enum TestEnumBackendString: string
{
    case FirstCase = 'a';
    case SecondCase = 'b';
}
