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

namespace Tests\Conjoon\MailClient\Message;

use Conjoon\Mime\MimeType;
use Conjoon\MailClient\Data\CompoundKey\MessageKey;
use Conjoon\MailClient\Message\AbstractMessageBody;
use Conjoon\MailClient\Message\MessageBodyDraft;
use Conjoon\MailClient\Message\MessagePart;
use Conjoon\Core\Contract\Jsonable;
use Tests\JsonableTestTrait;
use Tests\TestCase;

/**
 * Class MessageBodyDraftTest
 * @package Tests\Conjoon\MailClient\Message
 */
class MessageBodyDraftTest extends TestCase
{
    use JsonableTestTrait;


// ---------------------
//    Tests
// ---------------------
    /**
     * Test class
     */
    public function testClass()
    {

        $body = new MessageBodyDraft();

        $this->assertInstanceOf(Jsonable::class, $body);
        $this->assertInstanceOf(AbstractMessageBody::class, $body);

        $plainPart = new MessagePart("foo", "ISO-8859-1", MimeType::TEXT_PLAIN);
        $htmlPart = new MessagePart("<b>bar</b>", "UTF-8", MimeType::TEXT_HTML);

        $this->assertEquals([
            "type" => "MessageBody",
            "textPlain" => "",
            "textHtml" => ""
        ], $body->toArray());

        $body->setTextPlain($plainPart);

        $this->assertEquals([
            "type" => "MessageBody",
            "textPlain" => "foo",
            "textHtml" => ""
        ], $body->toArray());

        $body->setTextHtml($htmlPart);

        $this->assertEquals([
            "type" => "MessageBody",
            "textPlain" => "foo",
            "textHtml" => "<b>bar</b>"
        ], $body->toArray());

        $this->assertSame($plainPart, $body->getTextPlain());
        $this->assertSame($htmlPart, $body->getTextHtml());


        $body = new MessageBodyDraft(new MessageKey("a", "b", "c"));

        $this->assertEquals([
            "type" => "MessageBody",
            "mailAccountId" => "a",
            "mailFolderId" => "b",
            "id" => "c",
            "textPlain" => "",
            "textHtml" => ""
        ], $body->toArray());
    }


    /**
     * Test toJson
     */
    public function testToJson()
    {
        $body = new MessageBodyDraft();
        $this->runToJsonTest($body);
    }


    /**
     * Test setMessageKey()
     */
    public function testSetMessageKey()
    {

        $body = new MessageBodyDraft(new MessageKey("a", "b", "c"));

        $plainPart = new MessagePart("foo", "ISO-8859-1", MimeType::TEXT_PLAIN);
        $htmlPart = new MessagePart("<b>bar</b>", "UTF-8", MimeType::TEXT_HTML);

        $body->setTextPlain($plainPart);
        $body->setTextHtml($htmlPart);

        $newKey = new MessageKey("x", "y", "z");
        $copy = $body->setMessageKey($newKey);

        $this->assertNotSame($copy, $body);
        $this->assertSame($copy->getMessageKey(), $newKey);
        $this->assertEquals($copy->getTextPlain(), $body->getTextPlain());
        $this->assertEquals($copy->getTextHtml(), $body->getTextHtml());
    }
}
