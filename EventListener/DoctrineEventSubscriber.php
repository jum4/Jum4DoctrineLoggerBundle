<?php

namespace Jum4\DoctrineLoggerBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Jum4\DoctrineLoggerBundle\Logger\DoctrineLogger;
use Jum4\DoctrineLoggerBundle\Logger\EntityChangeSet;

/**
 * Class DoctrineEventSubscriber
 *
 * @author Julien Martin <julien.martin@jum4.org>
 */
class DoctrineEventSubscriber implements EventSubscriber
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'onFlush',
            'postFlush',
        ];
    }

    /**
     * @param OnFlushEventArgs $eventArgs
     */
    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();
        $logger = $this->getLogger();

        if ($logger->isActive()) {
            foreach ($uow->getScheduledEntityInsertions() as $entity) {
                $changeSet = $uow->getEntityChangeSet($entity);
                $logger->add(new EntityChangeSet($entity, DoctrineLogger::ACTION_INSERT, $changeSet));
            }

            foreach ($uow->getScheduledEntityUpdates() as $entity) {
                $logger->add(new EntityChangeSet($entity, DoctrineLogger::ACTION_UPDATE, $uow->getEntityChangeSet($entity)));
            }

            foreach ($uow->getScheduledEntityDeletions() as $entity) {
                $logger->add(new EntityChangeSet($entity, DoctrineLogger::ACTION_DELETE, $uow->getEntityChangeSet($entity)));
            }

            foreach ($uow->getScheduledCollectionDeletions() as $col) {
                $logger->add(new EntityChangeSet($col, DoctrineLogger::ACTION_COLLECTION_ADD));
            }

            foreach ($uow->getScheduledCollectionUpdates() as $col) {
                $logger->add(new EntityChangeSet($col, DoctrineLogger::ACTION_COLLECTION_REMOVE));
            }
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFLush(PostFlushEventArgs $args)
    {
        $this->getLogger()->write();
    }

    /**
     * @return DoctrineLogger
     * @throws \Exception
     */
    private function getLogger()
    {
        return $this->container->get('jum4_doctrine_logger.doctrine_logger');
    }
}
