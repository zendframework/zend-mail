<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mail\Protocol;

use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception\InvalidServiceException;
use Zend\ServiceManager\Factory\InvokableFactory;

/**
 * Plugin manager implementation for SMTP extensions.
 *
 * Enforces that SMTP extensions retrieved are instances of Smtp. Additionally,
 * it registers a number of default extensions available.
 */
class SmtpPluginManager extends AbstractPluginManager
{
    /**
     * @var string
     */
    protected $instanceOf = Smtp::class;

    /**
     * Default set of extensions
     *
     * @var array
     */
    protected $aliases = [
        'crammd5' => Smtp\Auth\Crammd5::class,
        'Crammd5' => Smtp\Auth\Crammd5::class,
        'login'   => Smtp\Auth\Login::class,
        'Login'   => Smtp\Auth\Login::class,
        'plain'   => Smtp\Auth\Plain::class,
        'Plain'   => Smtp\Auth\Plain::class,
        'smtp'    => Smtp::class,
        'Smtp'    => Smtp::class,
    ];

    protected $factories = [
        Smtp\Auth\Crammd5::class          => InvokableFactory::class,
        'zendmailprotocolsmtpauthcrammd5' => InvokableFactory::class,
        Smtp\Auth\Login::class            => InvokableFactory::class,
        'zendmailprotocolsmtpauthlogin'   => InvokableFactory::class,
        Smtp\Auth\Plain::class            => InvokableFactory::class,
        'zendmailprotocolsmtpauthplain'   => InvokableFactory::class,
        Smtp::class                       => InvokableFactory::class,
        'zendmailprotocolsmtp'            => InvokableFactory::class,
    ];

    /**
     * Validate the plugin is of the expected type (v3).
     *
     * Validates against `$instanceOf`.
     *
     * @param mixed $instance
     * @throws InvalidServiceException
     */
    public function validate($instance)
    {
        if (!$instance instanceof $this->instanceOf) {
            throw new InvalidServiceException(
                sprintf(
                    '%s can only create instances of %s; %s is invalid',
                    get_class($this),
                    $this->instanceOf,
                    (is_object($instance) ? get_class($instance) : gettype($instance))
                )
            );
        }
    }

    /**
     * Validate the plugin is of the expected type (v2).
     *
     * Proxies to `validate()`.
     *
     * @param mixed $instance
     * @throws Exception\InvalidArgumentException
     */
    public function validatePlugin($instance)
    {
        try {
            $this->validate($instance);
        } catch (InvalidServiceException $e) {
            throw new Exception\InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
