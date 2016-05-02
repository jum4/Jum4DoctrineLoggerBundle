<?php

namespace Jum4\DoctrineLoggerBundle\Tests\Logger;

use Jum4\DoctrineLoggerBundle\Builder\LogBuilder;
use Jum4\DoctrineLoggerBundle\Logger\DoctrineLogger;
use Jum4\DoctrineLoggerBundle\Logger\EntityChangeSet;
use Jum4\DoctrineLoggerBundle\Tests\Builder\TestEntity;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

/**
 * Class DoctrineLoggerTest
 * @author Julien Martin <julien.martin@jum4.org>
 */
class DoctrineLoggerTest extends \PHPUnit_Framework_TestCase
{
    /** @var object|LoggerInterface */
    private $logger;

    /** @var object|LogBuilder */
    private $logBuilder;

    public function testLog()
    {
        $rp = new \ReflectionProperty(DoctrineLogger::class, 'context');
        $rp->setAccessible(true);

        $logger = $this->getDoctrineLogger();

        $entity = new TestEntity();
        $logger->log($entity, 'create');

        /** @var EntityChangeSet $context */
        $context = $rp->getValue($logger);

        $this->assertEquals($entity, $context->getEntity());
        $this->assertEquals('create', $context->getAction());
    }

    public function testIsActive()
    {
        $logger = $this->getDoctrineLogger();

        $this->assertFalse($logger->isActive());

        $entity = new TestEntity();
        $logger->log($entity, 'create');

        $this->assertTrue($logger->isActive());
    }

    public function testAdd()
    {
        $rp = new \ReflectionProperty(DoctrineLogger::class, 'context');
        $rp->setAccessible(true);

        $logger = $this->getDoctrineLogger();

        $entity = new TestEntity();
        $logger->log($entity, 'create');


        /** @var EntityChangeSet $context */
        $context = $rp->getValue($logger);

        $rp = new \ReflectionProperty(EntityChangeSet::class, 'changeSets');
        $rp->setAccessible(true);

        $changeSet = $rp->getValue($context);
        $this->assertCount(0, $changeSet);

        $logger->add(new EntityChangeSet($entity, 'create'));

        $changeSet = $rp->getValue($context);
        $this->assertCount(1, $changeSet);
    }

    public function testWrite()
    {
        $logger = $this->getDoctrineLogger();

        $entity = new TestEntity();

        $this->logger
            ->log(Argument::exact('info'), Argument::exact('teststring'), Argument::exact(['changeSet' => [["TestEntity [create]" => []]]]))
            ->shouldBeCalled();

        $logger->log($entity, 'create');
        $logger->add(new EntityChangeSet($entity, 'create'));

        $logger->write();
    }

    /**
     * setUp
     */
    public function setUp()
    {
        $this->logger = $this->prophesize(LoggerInterface::class);
        $this->logBuilder = $this->prophesize(LogBuilder::class);
        $this->logBuilder->build(Argument::any())->willReturn('teststring');
    }

    /**
     * @return DoctrineLogger
     */
    private function getDoctrineLogger()
    {
        return new DoctrineLogger($this->logger->reveal(), $this->logBuilder->reveal(), 'info');
    }
}
