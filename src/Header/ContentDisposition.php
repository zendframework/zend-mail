<?php
/**
 * @see       https://github.com/zendframework/zend-mail for the canonical source repository
 * @copyright Copyright (c) 2005-2018 Zend Technologies USA Inc. (https://www.zend.com)
 * @license   https://github.com/zendframework/zend-mail/blob/master/LICENSE.md New BSD License
 */

namespace Zend\Mail\Header;

class ContentDisposition extends GenericHeader
{
    /**
     * @var string
     */
    protected $fieldName = 'Content-Disposition';

    /**
     * @var string
     */
    protected $fieldValue = 'inline';

    /**
     * Header encoding
     *
     * @var string
     */
    protected $encoding = 'ASCII';

    public function setEncoding($encoding)
    {
        $this->encoding = 'ASCII';
        return $this;
    }
}
