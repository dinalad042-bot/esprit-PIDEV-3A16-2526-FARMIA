<?php

namespace App\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DoctrineCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Get the Doctrine DBAL connection configuration
        $doctrineConfig = $container->getParameter('doctrine.dbal.connection_factory.class');
        
        // If using SQLite, remove use_savepoints
        $databaseUrl = $_ENV['DATABASE_URL'] ?? '';
        if (strpos($databaseUrl, 'sqlite') !== false) {
            // Remove use_savepoints from the configuration
            if ($container->hasParameter('doctrine.dbal.connection_factory.options')) {
                $options = $container->getParameter('doctrine.dbal.connection_factory.options');
                unset($options['use_savepoints']);
                $container->setParameter('doctrine.dbal.connection_factory.options', $options);
            }
        }
    }
}
