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

namespace Conjoon\Illuminate\Http\Query;

use Conjoon\Http\Query\Exception\InvalidParameterResourceException;
use Conjoon\Http\Query\ParameterList;
use Conjoon\Http\Query\Util;
use Conjoon\Http\Query\Query;
use Conjoon\Http\Query\Parameter;
use Illuminate\Http\Request;

/**
 * LaravelQuery representing queries of Requests with the type Illuminate\Http\Request.
 * This implementation makes also sure that if a group parameter (array notation with brackets)
 * was specified as a parameter name, this parameter name will be considered as a fqn
 * for its lookup.
 */
final class LaravelQuery implements Query
{
    /**
     * @var array
     */
    protected array $rawParameters = [];

    /**
     * @var array
     */
    protected array $parameterList = [];


    /**
     * @var ParameterList|null
     */
    protected ?ParameterList $list = null;


    /**
     * @var string|null
     */
    protected string $queryString;

    /**
     * Constructor.
     *
     * @param $parameterResource
     *
     * @throws InvalidParameterResourceException if the supplied argument is not
     * an instance of Illuminate\Http\Request
     */
    public function __construct($parameterResource)
    {
        if (!($parameterResource instanceof Request)) {
            throw new InvalidParameterResourceException(
                "Expected \"parameterResource\" to be instance of " . Request::class
            );
        }

        $this->queryString   = $parameterResource->getQueryString() ?? "";
        $this->rawParameters = $parameterResource->query();
    }


    /**
     * @inheritdoc
     */
    public function getParameter(string $name): ?Parameter
    {
        if (array_key_exists($name, $this->parameterList)) {
            return $this->parameterList[$name];
        }
        $exists = array_key_exists($name, $this->rawParameters) ;

        if ($exists && !is_array($this->rawParameters[$name])) {
            $this->parameterList[$name] = new Parameter($name, $this->rawParameters[$name]);
            return $this->parameterList[$name];
        }

        if (Util::isGroupParameter($name)) {
            $groupName = Util::getGroupName($name);

            if ($groupName) {
                $groups = $this->rawParameters[$groupName];

                if (is_array($groups)) {
                    foreach ($groups as $iName => $value) {
                        if ($name === $groupName . "[" . $iName . "]") {
                            $this->parameterList[$name] = new Parameter($name, $value);
                            break;
                        }
                    }
                }
            }
        }

        if (!array_key_exists($name, $this->parameterList)) {
            $this->parameterList[$name] = null;
        }

        return $this->parameterList[$name];
    }

    /**
     * @inheritdoc
     */
    public function getAllParameterNames(): array
    {
        return $this->getAllParameters()->map(fn ($param) => $param->getName());
    }


    /**
     * @return ParameterList
     */
    public function getAllParameters(): ParameterList
    {
        if ($this->list) {
            return $this->list;
        }

        $list = new ParameterList();

        foreach ($this->rawParameters as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $groupName => $groupValue) {
                    $fieldName = $key . "[" . $groupName . "]";
                    $list[] = $this->getParameter($fieldName);
                }
            } else {
                $list[] = $this->getParameter($key);
            }
        }

        $this->list = $list;
        return $list;
    }

    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return $this->toString();
    }

    /**
     * @inheritdoc
     */
    public function getSource(): object
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function toString(): string
    {
        return $this->queryString;
    }
}
