<?php

namespace Jum4\DoctrineLoggerBundle\Logger;

/**
 * Class EntityChangeSet
 * @author Julien Martin <julien.martin@jum4.org>
 */
class EntityChangeSet
{
    /** @var object */
    private $entity;

    /** @var string */
    private $action;

    /** @var array */
    private $changes;

    /** @var EntityChangeSet[] */
    private $changeSets;

    /**
     * EntityChangeSet constructor.
     *
     * @param mixed  $entity
     * @param string $action
     * @param array  $changes
     */
    public function __construct($entity, $action, $changes = [])
    {
        $this->entity     = $entity;
        $this->action     = $action;
        $this->changes    = $changes;
        $this->changeSets = [];
    }

    /**
     * @param EntityChangeSet $changeSet
     */
    public function add(EntityChangeSet $changeSet)
    {
        $this->changeSets[] = $changeSet;
    }

    /**
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        $entity = $this->getEntity();
        if (is_array($entity)) {
            $entity = reset($entity);
        }
        $rc = new \ReflectionClass($entity);

        return $rc->getShortName();
    }

    /**
     * @return bool
     */
    public function hasChilds()
    {
        return !empty($this->changeSets);
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return
            array_merge(
                array_map(function (EntityChangeSet $changeSet) {
                    return [
                        $changeSet->getDisplayName().' ['.$changeSet->getAction().']' => $changeSet->__toArray(),
                    ];
                }, $this->changeSets),
                $this->formatChanges()
            );
    }

    /**
     * @return array
     */
    private function formatChanges()
    {
        $formatted = [];
        foreach ($this->changes as $field => $change) {
            $formatted[$field] = [
                'before' => $change[0],
                'after'  => $change[1],
            ];
        }

        return $formatted;
    }
}
