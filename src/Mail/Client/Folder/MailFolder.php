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

namespace Conjoon\Mail\Client\Folder;

use Conjoon\Mail\Client\Data\CompoundKey\FolderKey;
use Conjoon\Core\Contract\Arrayable;
use Conjoon\Core\Contract\Jsonable;
use Conjoon\Core\Data\JsonStrategy;
use InvalidArgumentException;

/**
 * Class MailFolder models MailFolder-information for a specified MailAccount
 * in a tree structure, i.e. every MailFolder has a field "data" that contains
 * child MailFolder information.
 *
 * @example
 *
 *    $item = new MailFolder(
 *              new FolderKey("dev", "INBOX.SomeFolder"),
 *              [
 *                 "name"        => "INBOX.Some Folder",
 *                 "folderType"   => "INBOX"
 *                 "unreadMessages" => 4,
 *                 "totalMessages" => 756
 *              ]
 *            );
 *
 *    $listMailFolder->getDelimiter(); // "."
 *    $item->getUnreadCount(4);
 *
 *
 *
 * @package Conjoon\Mail\Client\Folder
 */
class MailFolder extends AbstractMailFolder implements Jsonable, Arrayable
{
    public const TYPE_INBOX = "INBOX";

    public const TYPE_DRAFT = "DRAFT";

    public const TYPE_JUNK = "JUNK";

    public const TYPE_TRASH = "TRASH";

    public const TYPE_SENT = "SENT";

    public const TYPE_FOLDER = "FOLDER";


    /**
     * @var string|null
     */
    protected ?string $folderType = null;

    /**
     * @var MailFolderChildList|null
     */
    protected ?MailFolderChildList $data = null;


    /**
     * Will initialize this MailFolderChildList except stated otherwise.
     *
     * @param FolderKey $folderKey
     * @param array $data
     */
    public function __construct(FolderKey $folderKey, array $data)
    {
        parent::__construct($folderKey, $data);

        if (!array_key_exists("data", $data)) {
            $this->data = new MailFolderChildList();
        }
    }


    /**
     * Sets the type of this folder.
     * @param string $folderType
     *
     * @throws InvalidArgumentException if $type has not a valid
     * value
     */
    public function setFolderType(?string $folderType)
    {

        if ($folderType === null) {
            $this->folderType = $folderType;
            return;
        }

        $types = [
            self::TYPE_INBOX, self::TYPE_DRAFT, self::TYPE_JUNK,
            self::TYPE_TRASH, self::TYPE_SENT, self::TYPE_FOLDER
        ];

        if (!in_array($folderType, $types)) {
            throw new InvalidArgumentException(
                "The value \"" . $folderType . "\" is not a valid type for a MailFolder"
            );
        }

        $this->folderType = $folderType;
    }


    /**
     * Returns the type of this folder.
     * @return string|null
     */
    public function getFolderType(): ?string
    {
        return $this->folderType;
    }


    /**
     * Returns the data of this folder, i.e. the child folders.
     *
     * @return MailFolderChildList
     */
    public function getData(): ?MailFolderChildList
    {
        return $this->data;
    }


    /**
     * Setter for data. Will allow for passing null to explicitely nullify
     * this mail folders child list, so it may not be considered when transforming
     * to a DTO.
     *
     * @param MailFolder|null $mailFolder
     *
     * @return void
     */
    public function setData(?MailFolder $mailFolder): ?MailFolder
    {
        if ($mailFolder === null) {
            $this->data = null;
            return null;
        }

        return $this->addMailFolder($mailFolder);
    }


    /**
     * Adds a new child MailFolder to this folder.
     *
     * @param MailFolder $mailFolder
     *
     * @return MailFolder the added MailFolder
     */
    public function addMailFolder(MailFolder $mailFolder): MailFolder
    {
        if (!$this->data) {
            $this->data = new MailFolderChildList();
        }

        $this->data[] = $mailFolder;

        return $mailFolder;
    }

// +-------------------------------
// | Arrayable interface
// +-------------------------------

    /**
     * @inheritdoc
     */
    public function toArray(): array
    {
        $id = $this->getFolderKey()->getId();

        $type = "MailFolder";

        $arr = [
            "type" => $type,
            "name" => $this->getName(),
            "unreadMessages" => $this->getUnreadMessages(),
            "totalMessages" => $this->getTotalMessages(),
            "folderType" => $this->getFolderType(),
            "data" => $this->getData() ? $this->getData()->toArray() : null
        ];

        return array_filter(
            array_merge($this->getFolderKey()->toArray(), $arr),
            fn($value) => $value !== null
        );
    }

// +-------------------------------
// | Jsonable interface
// +-------------------------------

    /**
     * @inheritdoc
     */
    public function toJson(JsonStrategy $strategy = null): array
    {
        return $strategy ? $strategy->toJson($this) : $this->toArray();
    }
}
