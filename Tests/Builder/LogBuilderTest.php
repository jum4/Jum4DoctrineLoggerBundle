<?php

namespace Jum4\DoctrineLoggerBundle\Tests\Builder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Jum4\DoctrineLoggerBundle\Builder\LogBuilder;
use Jum4\DoctrineLoggerBundle\Logger\EntityChangeSet;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class LogBuilderTest
 *
 * @author Julien Martin <julien.martin@jum4.org>
 */
class LogBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var object|TokenStorage
     */
    private $tokenStorage;

    /**
     * @var object|EntityManager
     */
    private $em;

    /**
     * @var object|UnitOfWork
     */
    private $uow;

    public function testBuildWithEntity()
    {
        $entity = new \stdClass();
        $changeSet = new EntityChangeSet($entity, 'CREATE', []);

        $this->uow->isInIdentityMap(Argument::any())->willReturn(true);
        $this->uow->getEntityIdentifier(Argument::any())->willReturn(['id' => 1]);

        $expected = "[CREATE] stdClass 1 by Anonymous";
        $result = $this->getBuilder()->build($changeSet);

        $this->assertEquals($expected, $result);
    }

    public function testBuildWithEntityWithToString()
    {
        $entity = new TestEntity();

        $changeSet = new EntityChangeSet($entity, 'CREATE', []);

        $this->uow->isInIdentityMap(Argument::any())->willReturn(true);
        $this->uow->getEntityIdentifier(Argument::any())->willReturn(['id' => 1]);

        $expected = "[CREATE] test by Anonymous";
        $result = $this->getBuilder()->build($changeSet);

        $this->assertEquals($expected, $result);
    }

    public function testBuildWithArray()
    {
        $entity = [new \stdClass()];
        $changeSet = new EntityChangeSet($entity, 'CREATE', []);

        $this->uow->isInIdentityMap(Argument::any())->willReturn(false);

        $expected = "[CREATE] stdClass  by Anonymous";
        $result = $this->getBuilder()->build($changeSet);

        $this->assertEquals($expected, $result);
    }

    public function setUp()
    {
        $this->uow = $this->prophesize(UnitOfWork::class);
        $this->tokenStorage = $this->prophesize(TokenStorage::class);
        $this->em = $this->prophesize(EntityManager::class);
        $this->em->getUnitOfWork()->willReturn($this->uow->reveal());
    }

    /**
     * @return LogBuilder
     */
    private function getBuilder()
    {
        return new LogBuilder($this->tokenStorage->reveal(), $this->em->reveal());
    }
}