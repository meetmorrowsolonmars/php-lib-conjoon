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

namespace Tests\Conjoon\Http\Query\Validation\Parameter;

use Conjoon\Core\Validation\ValidationErrors;
use Conjoon\Http\Query\Parameter;
use Conjoon\Http\Query\Validation\Parameter\JsonEncodedRule;
use Conjoon\Http\Query\Validation\Parameter\NamedParameterRule;
use Tests\TestCase;

/**
 * Tests JsonEncodedRule.
 */
class JsonEncodedRuleTest extends TestCase
{
    /**
     * Class functionality
     */
    public function testClass()
    {
        $rule = new JsonEncodedRule("filter");
        $this->assertInstanceOf(NamedParameterRule::class, $rule);
    }


    /**
     * tests validate()
     */
    public function testValidate()
    {
        $errors = new ValidationErrors();

        // simple validate type
        $rule = new JsonEncodedRule("filter");
        $this->assertFalse($rule->isValid(new Parameter("filter", "string_value"), $errors));
        $this->assertStringContainsString("Could not decode", $errors->peek()->getDetails());

        $rule = new JsonEncodedRule("filter");
        $this->assertTrue($rule->isValid(new Parameter("filter", json_encode(["key" => "value"])), $errors));
    }
}
