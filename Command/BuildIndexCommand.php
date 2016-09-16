<?php

namespace FlexModel\FlexModelElasticsearchBundle\Command;

use Elasticsearch\Client;
use FlexModel\FlexModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * (Re-)builds the Elasticsearch index.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class BuildIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('flexmodel:elasticsearch:build-index')
            ->setDescription('(Re-)builds the Elasticsearch index.');
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $indexName = $this->getContainer()
            ->getParameter('flex_model.elasticsearch.index.name');

        $io->section(sprintf('(Re-)building Elasticsearch index: %s', $indexName));

        $client = $this->getContainer()->get('flex_model.elasticsearch.client');
        /* @var $client Client */

        $indexParameters = array(
            'index' => $indexName,
        );
        if ($client->indices()->exists($indexParameters)) {
            $io->text(sprintf('Elasticsearch index "%s" already exists. Deleting...', $indexName));

            $client->indices()->delete($indexParameters);
        }

        $result = $client->indices()->create($this->getIndexCreationParameters($indexName));
        if (isset($result['acknowledged']) && $result['acknowledged'] === true) {
            $io->success('Created Elasticsearch index.');

            $command = $this->getApplication()
                ->find('flexmodel:elasticsearch:update-index');

            return $command->run($input, $output);
        }

        $this->block('Failed creating Elasticsearch index.', 'FAILURE', 'fg=white;bg=red', ' ', true);
    }

    /**
     * Returns the array with parameters for creating an Elasticsearch index with the proper settings and mappings.
     *
     * @param string $indexName
     *
     * @return array
     */
    private function getIndexCreationParameters($indexName)
    {
        $parameters = array(
            'index' => $indexName,
            'body' => array(
                'settings' => $this->getContainer()
                    ->getParameter('flex_model.elasticsearch.index.settings'),
            ),
        );

        $indexMappings = array();

        $flexModel = $this->getContainer()->get('flexmodel');
        /* @var $flexModel FlexModel */

        $objectNames = $flexModel->getObjectNames();
        foreach ($objectNames as $objectName) {
            $viewConfiguration = $flexModel->getViewConfiguration($objectName, 'searchindex');
            if (isset($viewConfiguration)) {
                foreach ($viewConfiguration['fields'] as $fieldConfiguration) {
                    if (isset($fieldConfiguration['options'])) {
                        $indexMappings[$objectName]['properties'][$fieldConfiguration['name']] = array(
                            'type' => $this->getElasticsearchDatatype($fieldConfiguration['datatype']),
                            'index' => 'not_analyzed',
                        );
                    }
                }
            }
        }

        $parameters['body']['mappings'] = array_replace_recursive(
            $indexMappings,
            $this->getContainer()
                ->getParameter('flex_model.elasticsearch.index.mappings')
        );

        return $parameters;
    }

    /**
     * Returns the FlexModel datatype equivalent for Elasticsearch.
     *
     * @param string $flexModelDatatype
     *
     * @return string
     */
    private function getElasticsearchDatatype($flexModelDatatype)
    {
        $datatype = strtolower($flexModelDatatype);
        switch ($flexModelDatatype) {
            case 'DATETIME':
                $datatype = 'date';
            case 'DATEINTERVAL':
            case 'HTML':
            case 'JSON':
            case 'SET':
            case 'TEXT':
            case 'VARCHAR':
                $datatype = 'string';
                break;
            case 'DECIMAL':
                $datatype = 'double';
                break;
            case 'FILE':
                $datatype = 'binary';
                break;
        }

        return $datatype;
    }
}
