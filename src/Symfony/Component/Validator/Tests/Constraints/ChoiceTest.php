<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Tests\Constraints;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Mapping\Loader\AttributeLoader;
use Symfony\Component\Validator\Tests\Fixtures\ConstraintChoiceWithPreset;
use Symfony\Component\Validator\Tests\Fixtures\TestEnumBackendInteger;
use Symfony\Component\Validator\Tests\Fixtures\TestEnumBackendString;
use Symfony\Component\Validator\Tests\Fixtures\TestEnumUnit;

class ChoiceTest extends TestCase
{
    public function testNormalizerCanBeSet()
    {
        $choice = new Choice(normalizer: 'trim');

        $this->assertEquals(trim(...), $choice->normalizer);
    }

    public function testSetDefaultPropertyChoice()
    {
        $constraint = new ConstraintChoiceWithPreset('A');

        self::assertEquals(['A', 'B', 'C'], $constraint->choices);
    }

    public function testAttributes()
    {
        $metadata = new ClassMetadata(ChoiceDummy::class);
        $loader = new AttributeLoader();
        self::assertTrue($loader->loadClassMetadata($metadata));

        /** @var Choice $aConstraint */
        [$aConstraint] = $metadata->properties['a']->getConstraints();
        self::assertSame([1, 2], $aConstraint->choices);
        self::assertSame(['Default', 'ChoiceDummy'], $aConstraint->groups);

        /** @var Choice $bConstraint */
        [$bConstraint] = $metadata->properties['b']->getConstraints();
        self::assertSame(['foo', 'bar'], $bConstraint->choices);
        self::assertSame('myMessage', $bConstraint->message);
        self::assertSame(['Default', 'ChoiceDummy'], $bConstraint->groups);

        /** @var Choice $cConstraint */
        [$cConstraint] = $metadata->properties['c']->getConstraints();
        self::assertSame([1, 2], $aConstraint->choices);
        self::assertSame(['my_group'], $cConstraint->groups);
        self::assertSame('some attached data', $cConstraint->payload);

        /** @var Choice $stringIndexedConstraint */
        [$stringIndexedConstraint] = $metadata->properties['stringIndexed']->getConstraints();
        self::assertSame(['one' => 1, 'two' => 2], $stringIndexedConstraint->choices);

        /** @var Choice $enumUnitConstraint */
        [$enumUnitConstraint] = $metadata->properties['enumUnit']->getConstraints();
        self::assertSame(TestEnumUnit::class, $enumUnitConstraint->choices);

        /** @var Choice $enumBackendStringConstraint */
        [$enumBackendStringConstraint] = $metadata->properties['enumBackendString']->getConstraints();
        self::assertSame(TestEnumBackendString::class, $enumBackendStringConstraint->choices);

        /** @var Choice $enumBackendIntegerConstraint */
        [$enumBackendIntegerConstraint] = $metadata->properties['enumBackendInteger']->getConstraints();
        self::assertSame(TestEnumBackendInteger::class, $enumBackendIntegerConstraint->choices);
    }
}

class ChoiceDummy
{
    #[Choice(choices: [1, 2])]
    private $a;

    #[Choice(choices: ['foo', 'bar'], message: 'myMessage')]
    private $b;

    #[Choice([1, 2], groups: ['my_group'], payload: 'some attached data')]
    private $c;

    #[Choice(choices: ['one' => 1, 'two' => 2])]
    private $stringIndexed;

    #[Choice(choices: TestEnumUnit::class)]
    private $enumUnit;

    #[Choice(choices: TestEnumBackendString::class)]
    private $enumBackendString;

    #[Choice(choices: TestEnumBackendInteger::class)]
    private $enumBackendInteger;
}
