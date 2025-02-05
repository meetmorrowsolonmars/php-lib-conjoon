<?php

/**
 * conjoon
 * php-lib-conjoon
 * Copyright (C) 2019-2022 Thorsten Suckow-Homberg https://github.com/conjoon/php-lib-conjoon
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

namespace Tests\Conjoon\Mail\Client\Data;

use BadMethodCallException;
use Conjoon\Mail\Client\Data\MailAccount;
use Conjoon\Mail\Client\Util\JsonApiStrategy;
use Conjoon\Util\Arrayable;
use Conjoon\Util\Jsonable;
use Conjoon\Util\JsonStrategy;
use Tests\TestCase;

/**
 * Class MailAccountTest
 * @package Tests\Conjoon\Mail\Client\Data
 */
class MailAccountTest extends TestCase
{
    protected array $accountConfig = [
        "id"              => "dev_sys_conjoon_org",
        "name"            => "conjoon developer",
        "from"            => ["name" => "John Smith", "address" => "dev@conjoon.org"],
        "replyTo"         => ["name" => "John Smith", "address" => "dev@conjoon.org"],
        "inbox_type"      => "IMAP",
        "inbox_address"   => "some.address.server",
        "inbox_port"      => 993,
        "inbox_user"      => "user inbox",
        "inbox_password"  => "password",
        "inbox_ssl"       => true,
        "outbox_address"  => "some.outbox.server",
        "outbox_port"     => 993,
        "outbox_user"     => "user",
        "outbox_password" => "password outbox",
        "outbox_secure"   => "ssl",
        "root"            => ["[Gmail]"]
    ];


    /**
     * @return void
     */
    public function testInstance()
    {
        $account = new MailAccount($this->accountConfig);

        $this->assertInstanceOf(Jsonable::class, $account);
        $this->assertInstanceOf(Arrayable::class, $account);
    }

    /**
     * magic methods
     */
    public function testGetter()
    {
        $config = $this->accountConfig;

        $oldRoot = $config["root"];
        $this->assertSame(["[Gmail]"], $oldRoot);
        unset($config["root"]);

        $account = new MailAccount($config);
        $this->assertSame(["INBOX"], $account->getRoot());

        $config["root"] = $oldRoot;
        $account = new MailAccount($config);

        foreach ($config as $property => $value) {
            if ($property === "from" || $property === "replyTo") {
                $method = $property == "from" ? "getFrom" : "getReplyTo";
            } else {
                $camelKey = "_" . str_replace("_", " ", strtolower($property));
                $camelKey = ltrim(str_replace(" ", "", ucwords($camelKey)), "_");
                $method   = "get" . ucfirst($camelKey);
            }

            $this->assertSame($value, $account->{$method}());
        }
    }


    /**
     * not existing method
     *
     * @noinspection PhpUndefinedMethodInspection
     */
    public function testGetterException()
    {

        $config = $this->accountConfig;
        $account = new MailAccount($config);

        $this->expectException(BadMethodCallException::class);

        $account->getSomeFoo();
    }


    /**
     * toArray()
     */
    public function testToArray()
    {
        $config = $this->accountConfig;
        $config["type"] = "MailAccount";

        $account = new MailAccount($config);

        $this->assertEquals($config, $account->toArray());
    }


    /**
     * toArray()
     */
    public function testToJson()
    {
        $config = $this->accountConfig;
        $config["type"] = "MailAccount";

        $account = new MailAccount($config);

        $this->assertEquals($config, $account->toJson());

        $strategyMock =
            $this->getMockBuilder(JsonStrategy::class)
                 ->getMockForAbstractClass();

        $strategyMock
            ->expects($this->exactly(1))
            ->method("toJson")
            ->with($account)
            ->willReturn($account->toArray());

        $this->assertEquals($config, $account->toJson($strategyMock));
    }
}
