<?php

namespace FlexModel\FlexModelElasticsearchBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Indexer;
use FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Model\IndexableObjectInterface;

/**
 * ObjectIndexSubscriber.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class ObjectIndexerSubscriber implements EventSubscriber
{
    /**
     * The Indexer instance.
     *
     * @var Indexer
     */
    private $indexer;

    /**
     * Constructs a new ObjectIndexerSubscriber instance.
     *
     * @param Indexer $indexer
     */
    public function __constuct(Indexer $indexer) {
        $this->indexer = $indexer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return array(
            'postUpdate',
            'postPersist',
        );
    }

    /**
     * Calls the Elasticsearch Indexer to index the object.
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postPersist($args);
    }

    /**
     * Calls the Elasticsearch Indexer to index the object.
     *
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $object = $args->getObject();
        $objectChangeset = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($object);
        if ($object instanceof IndexableObjectInterface && count($objectChangeset) > 1) {
            $this->indexer->indexObject($object);
        }
    }
}
