<?php

namespace Jum4\DoctrineLoggerBundle\Logger;

use Jum4\DoctrineLoggerBundle\Builder\LogBuilder;
use Psr\Log\LoggerInterface;

/**
 * Class DoctrineLogger
 *
 * @author Julien Martin <julien.martin@jum4.org>
 */
class DoctrineLogger
{
    const ACTION_INSERT = 'CREATE';
    const ACTION_UPDATE = 'UPDATE';
    const ACTION_DELETE = 'DELETE';
    const ACTION_COLLECTION_ADD = 'ADD';
    const ACTION_COLLECTION_REMOVE = 'REMOVE';

    private $level;
    private $logger;
    private $logBuilder;

    /** @var EntityChangeSet */
    private $context;

    /**
     * DoctrineLogger constructor.
     *
     * @param LoggerInterface $logger
     * @param LogBuilder      $logBuilder
     * @param string          $level
     */
    public function __construct(LoggerInterface $logger, LogBuilder $logBuilder, $level)
    {
        $this->logBuilder = $logBuilder;
        $this->logger     = $logger;
        $this->level      = $level;
    }

    /**
     * @param mixed  $entity
     * @param string $action
     */
    public function log($entity, $action)
    {
        $this->context = new EntityChangeSet($entity, $action);
    }

    /**
     * @param EntityChangeSet $changeSet
     */
    public function add(EntityChangeSet $changeSet)
    {
        if ($this->isActive()) {
            $this->context->add($changeSet);

            if (method_exists($this->context->getEntity(), 'setUpdated')) {
                $this->context->getEntity()->setUpdated();
            }
        }
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->context !== null;
    }

    /**
     * write
     */
    public function write()
    {
        if ($this->isActive() && $this->context->hasChilds()) {
            $this->logger->log(
                $this->level,
                $this->logBuilder->build($this->context),
                ['changeSet' => $this->context->__toArray()]
            );

            $this->context = null;
        }
    }
}
