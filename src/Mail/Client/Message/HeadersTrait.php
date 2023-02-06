<?php

namespace Conjoon\Mail\Client\Message;

// TODO: write docs
// TODO: rename to read only


/**
 * @method getHeaders()
 */
trait HeadersTrait
{
    protected ?array $headers = null;

    public function setHeaders(array $headers = null): AbstractMessageItem
    {
        $this->addModified("headers");
        $this->headers = $headers;
        return $this;
    }

}