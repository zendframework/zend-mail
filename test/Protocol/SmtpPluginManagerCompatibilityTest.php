<?php

namespace ZendTest\Mail\Protocol;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Mail\Exception\InvalidArgumentException;
use Zend\Mail\Protocol\Smtp;
use Zend\Mail\Protocol\SmtpPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Test\CommonPluginManagerTrait;

class PluginManagerCompatibilityTest extends TestCase
{
    use CommonPluginManagerTrait;

    protected function getPluginManager()
    {
        return new SmtpPluginManager(new ServiceManager());
    }

    protected function getV2InvalidPluginException()
    {
        return InvalidArgumentException::class;
    }

    protected function getInstanceOf()
    {
        return Smtp::class;
    }
}
