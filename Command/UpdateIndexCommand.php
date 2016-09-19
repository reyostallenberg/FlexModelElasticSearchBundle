<?php

namespace FlexModel\FlexModelElasticsearchBundle\Command;

use Doctrine\ORM\EntityManager;
use FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Indexer;
use FlexModel\FlexModelElasticsearchBundle\Elasticsearch\Model\IndexableObjectInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Updates the Elasticsearch index with available objects from the database.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class UpdateIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('flexmodel:elasticsearch:update-index')
            ->setDescription('Updates the Elasticsearch index with available objects from the database.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $client = $this->getContainer()->get('flex_model.elasticsearch.client');
        /* @var $client \Elasticsearch\Client */
        $indexName = $this->getContainer()->getParameter('flex_model.elasticsearch.index.name');
        $indexParameters = array('index' => $indexName);
        if ($client->indices()->exists($indexParameters) === false) {
            throw new RuntimeException(sprintf('Elasticsearch index "%s" does not exist.', $indexName));
        }

        $io->section(sprintf('Updating Elasticsearch index: %s', $indexName));

        $classMetaDataInstances = $this->getContainer()
            ->get('doctrine')
            ->getManager()
            ->getMetadataFactory()
            ->getAllMetadata();

        foreach ($classMetaDataInstances as $classMetaDataInstance) {
            if (is_subclass_of($classMetaDataInstance->getName(), IndexableObjectInterface::class)) {
                $io->title(sprintf('Indexing "%s" objects.', $classMetaDataInstance->getName()));

                $indexer = $this->getContainer()
                    ->get('flex_model.elasticsearch.indexer');
                /* @var $indexer Indexer */

                $entityManager = $this->getContainer()
                    ->get('doctrine')
                    ->getManager();
                /* @var $entityManager EntityManager */
                $count = $entityManager->createQueryBuilder()
                    ->select('count(o.id)')
                    ->from($classMetaDataInstance->getName(), 'o')
                    ->getQuery()
                    ->getSingleScalarResult();

                $query = $entityManager->createQueryBuilder()
                    ->select('o')
                    ->from($classMetaDataInstance->getName(), 'o')
                    ->getQuery();

                $io->progressStart($count);

                $result = $query->iterate();
                foreach ($result as $row) {
                    $indexer->indexObject($row[0]);

                    $io->progressAdvance();
                }

                $io->progressFinish();
            }
        }

        $io->success('Indexed all objects.');
    }
}
