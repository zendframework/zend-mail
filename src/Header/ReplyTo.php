<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Header;

class ReplyTo extends AbstractAddressList
{
    protected $fieldName = 'Reply-To';
    protected static $type = 'reply-to';

    protected static function cleanFieldName($fieldName)
    {
        $allowed = [
            'replyto', 'reply_to'
        ];

        foreach ($allowed as $name) {
            if (strtolower($fieldName) === $name) {
                return static::$type;
            }
        }

        return $fieldName;
    }
}
