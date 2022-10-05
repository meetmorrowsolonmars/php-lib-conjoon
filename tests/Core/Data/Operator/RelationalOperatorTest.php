<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge,
 * publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Tests\Conjoon\Core\Data\Operator;

use Conjoon\Core\Data\Operator\Operator;
use Conjoon\Core\Data\Operator\OperatorStringableTrait;
use Conjoon\Core\Data\Operator\RelationalOperator;
use Conjoon\Core\Data\StringStrategy;
use ReflectionClass;
use Tests\StringableTestTrait;
use Tests\TestCase;

/**
 * Tests RelationalOperator.
 */
class RelationalOperatorTest extends TestCase
{
// ---------------------
//    Tests
// ---------------------

    /**
     * Tests class
     */
    public function testClass()
    {
        $uses = class_uses(RelationalOperator::class);
        $this->assertContains(OperatorStringableTrait::class, $uses);

        $class = new ReflectionClass(RelationalOperator::class);
        $this->assertTrue($class->implementsInterface(Operator::class));


        $this->assertEqualsCanonicalizing(
            [
                RelationalOperator::IS,
                RelationalOperator::IS_NOT,
                RelationalOperator::GREATER_THAN,
                RelationalOperator::LESS_THAN,
                RelationalOperator::LESS_THAN_OR_EQUAL,
                RelationalOperator::GREATER_THAN_OR_EQUAL,
            ],
            RelationalOperator::cases()
        );

        $this->assertSame("=", RelationalOperator::IS->value);

        $this->assertSame("!=", RelationalOperator::IS_NOT->value);

        $this->assertSame(">", RelationalOperator::GREATER_THAN->value);

        $this->assertSame("<", RelationalOperator::LESS_THAN->value);

        $this->assertSame(">=", RelationalOperator::GREATER_THAN_OR_EQUAL->value);

        $this->assertSame("<=", RelationalOperator::LESS_THAN_OR_EQUAL->value);
    }
}
