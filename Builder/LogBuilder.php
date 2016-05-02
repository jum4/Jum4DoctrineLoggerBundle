<?php

namespace Jum4\DoctrineLoggerBundle\Builder;

use Doctrine\ORM\EntityManager;
use Jum4\DoctrineLoggerBundle\Logger\EntityChangeSet;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class LogBuilder
 * @author Julien Martin <julien.martin@jum4.org>
 */
class LogBuilder
{
    /** @var TokenStorage */
    private $tokenStorage;

    /** @var EntityManager */
    private $entityManager;

    /**
     * LogBuilder constructor.
     *
     * @param TokenStorage  $tokenStorage
     * @param EntityManager $entityManager
     */
    public function __construct(TokenStorage $tokenStorage, EntityManager $entityManager)
    {
        $this->tokenStorage  = $tokenStorage;
        $this->entityManager = $entityManager;
    }

    /**
     * @param EntityChangeSet $changeSet
     *
     * @return string
     */
    public function build(EntityChangeSet $changeSet)
    {
        return sprintf(
            '[%s] %s by %s',
            $changeSet->getAction(),
            $this->getDisplayName($changeSet),
            $this->getUser()
        );
    }

    /**
     * @param EntityChangeSet $changeSet
     *
     * @return string
     */
    private function getDisplayName(EntityChangeSet $changeSet)
    {
        if (method_exists($changeSet->getEntity(), '__toString')) {
            return $changeSet->getEntity()->__toString();
        }

        return $changeSet->getDisplayName().' '.$this->getIdentifier($changeSet);
    }


    /**
     * @param EntityChangeSet $changeSet
     *
     * @return string
     */
    private function getIdentifier(EntityChangeSet $changeSet)
    {
        if (is_object($changeSet->getEntity()) && $this->entityManager->getUnitOfWork()->isInIdentityMap($changeSet->getEntity())) {
            return implode(', ', $this->entityManager->getUnitOfWork()->getEntityIdentifier($changeSet->getEntity()));
        }

        return '';
    }

    /**
     * @return mixed|string
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken() ? $this->tokenStorage->getToken()->getUser() : 'Anonymous';
    }
}
