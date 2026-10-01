<?php

declare(strict_types=1);

namespace Koco\Kafka\DependencyInjection;

use Koco\Kafka\Messenger\KafkaTransportFactory;
use Koco\Kafka\Messenger\RestProxyTransportFactory;
use Koco\Kafka\RdKafka\RdKafkaFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

class KocoKafkaExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->setDefinition(RdKafkaFactory::class, (new Definition(RdKafkaFactory::class))->setPublic(false));

        $container->setDefinition(
            KafkaTransportFactory::class,
            (new Definition(KafkaTransportFactory::class))
                ->setPublic(false)
                ->addTag('messenger.transport_factory')
                ->setArguments([
                    new Reference(RdKafkaFactory::class),
                    self::optional('logger'),
                ])
        );

        $container->setDefinition(
            RestProxyTransportFactory::class,
            (new Definition(RestProxyTransportFactory::class))
                ->setPublic(false)
                ->addTag('messenger.transport_factory')
                ->setArguments([
                    self::optional('logger'),
                    self::optional(ClientInterface::class),
                    self::optional(RequestFactoryInterface::class),
                    self::optional(UriFactoryInterface::class),
                    self::optional(StreamFactoryInterface::class),
                ])
        );
    }

    private static function optional(string $id): Reference
    {
        return new Reference($id, ContainerInterface::NULL_ON_INVALID_REFERENCE);
    }
}
