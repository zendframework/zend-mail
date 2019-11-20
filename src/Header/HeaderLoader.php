<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

class HeaderLoader
{
    /**
     * @var array Pre-aliased Header classes
     */
    private $headerClassMap = [
        'bcc'                       => Bcc::class,
        'cc'                        => Cc::class,
        'contenttype'               => ContentType::class,
        'content_type'              => ContentType::class,
        'content-type'              => ContentType::class,
        'contenttransferencoding'   => ContentTransferEncoding::class,
        'content_transfer_encoding' => ContentTransferEncoding::class,
        'content-transfer-encoding' => ContentTransferEncoding::class,
        'date'                      => Date::class,
        'from'                      => From::class,
        'message-id'                => MessageId::class,
        'mimeversion'               => MimeVersion::class,
        'mime_version'              => MimeVersion::class,
        'mime-version'              => MimeVersion::class,
        'received'                  => Received::class,
        'replyto'                   => ReplyTo::class,
        'reply_to'                  => ReplyTo::class,
        'reply-to'                  => ReplyTo::class,
        'sender'                    => Sender::class,
        'subject'                   => Subject::class,
        'to'                        => To::class,
    ];

    /**
     * @param string $name
     * @param string|null $default
     * @return string|null
     */
    public function get($name, $default = null)
    {
        $name = $this->normalizeName($name);
        return isset($this->headerClassMap[$name]) ? $this->headerClassMap[$name] : $default;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->headerClassMap[$this->normalizeName($name)]);
    }

    public function add($name, $class)
    {
        $this->headerClassMap[$this->normalizeName($name)] = $class;
    }

    public function remove($name)
    {
        unset($this->headerClassMap[$this->normalizeName($name)]);
    }

    private function normalizeName($name)
    {
        return strtolower($name);
    }
}
