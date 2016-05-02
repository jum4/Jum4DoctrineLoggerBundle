<?php

namespace Jum4\DoctrineLoggerBundle\Tests\Logger;

use Jum4\DoctrineLoggerBundle\Logger\EntityChangeSet;
use Jum4\DoctrineLoggerBundle\Tests\Builder\TestEntity;

/**
 * Class EntityChangeSetTest
 * @author Julien Martin <julien.martin@jum4.org>
 */
class EntityChangeSetTest extends \PHPUnit_Framework_TestCase
{
    public function testContructor()
    {
        $entity = new TestEntity();
        $action = 'test';
        $changes = ['property' => 'value'];
        $changeSet = new EntityChangeSet($entity, $action, $changes);

        $rc = new \ReflectionProperty(EntityChangeSet::class, 'entity');
        $rc->setAccessible(true);
        $this->assertEquals($entity, $rc->getValue($changeSet));

        $rc = new \ReflectionProperty(EntityChangeSet::class, 'action');
        $rc->setAccessible(true);
        $this->assertEquals($action, $rc->getValue($changeSet));

        $rc = new \ReflectionProperty(EntityChangeSet::class, 'changes');
        $rc->setAccessible(true);
        $this->assertEquals($changes, $rc->getValue($changeSet));

        $rc = new \ReflectionProperty(EntityChangeSet::class, 'changeSets');
        $rc->setAccessible(true);
        $this->assertCount(0, $rc->getValue($changeSet));
    }

    public function testAdd()
    {
        $rc = new \ReflectionProperty(EntityChangeSet::class, 'changeSets');
        $rc->setAccessible(true);

        $changeSet = new EntityChangeSet(new TestEntity(), 'test');
        $this->assertCount(0, $rc->getValue($changeSet));

        $changeSet->add(new EntityChangeSet(new TestEntity(), 'test'));
        $this->assertCount(1, $rc->getValue($changeSet));
    }

    public function testGetAction()
    {
        $changeSet = new EntityChangeSet(new TestEntity(), 'test');

        $this->assertEquals('test', $changeSet->getAction());
    }

    public function testGetEntity()
    {
        $entity = new TestEntity();
        $changeSet = new EntityChangeSet($entity, 'test');

        $this->assertEquals($entity, $changeSet->getEntity());
    }

    public function testGetDisplayName()
    {
        $entity = new TestEntity();
        $changeSet = new EntityChangeSet([$entity], 'test');

        $this->assertEquals('TestEntity', $changeSet->getDisplayName());
    }

    public function testHasChileds()
    {
        $changeSet = new EntityChangeSet(new TestEntity(), 'test');
        $this->assertFalse($changeSet->hasChilds());

        $changeSet->add(new EntityChangeSet(new TestEntity(), 'test'));
        $this->assertTrue($changeSet->hasChilds());
    }

    public function testToArray()
    {
        $changeSet = new EntityChangeSet(new TestEntity(), 'test');
        $this->assertFalse($changeSet->hasChilds());

        $changeSet->add(new EntityChangeSet(new TestEntity(), 'test', ['property' => ['value1', 'value2']]));
        $this->assertTrue($changeSet->hasChilds());

        $expected =
        [
            [
                'TestEntity [test]' => [
                    'property' => [
                        'before' => 'value1',
                        'after'  => 'value2',
                    ]
                ]
            ]
        ];

        $result = $changeSet->__toArray();

        $this->assertEquals($expected, $result);
    }
}
