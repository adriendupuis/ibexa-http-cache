<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformHttpCacheBundle\EventSubscriber\CachePurge;

use EzSystems\PlatformHttpCacheBundle\PurgeClient\PurgeClientInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use eZ\Publish\SPI\Persistence\Content\Location\Handler as LocationHandler;
use eZ\Publish\SPI\Persistence\URL\Handler as UrlHandler;

/**
 * @internal
 */
abstract class AbstractSubscriber implements EventSubscriberInterface
{
    /** @var \EzSystems\PlatformHttpCacheBundle\PurgeClient\PurgeClientInterface */
    protected $purgeClient;

    /** @var \eZ\Publish\SPI\Persistence\Content\Location\Handler */
    private $locationHandler;

    /** @var \eZ\Publish\SPI\Persistence\URL\Handler */
    private $urlHandler;

    public function __construct(
        PurgeClientInterface $purgeClient,
        LocationHandler $locationHandler,
        UrlHandler $urlHandler
    ) {
        $this->purgeClient = $purgeClient;
        $this->locationHandler = $locationHandler;
        $this->urlHandler = $urlHandler;
    }

    public function getContentTags(int $contentId): array
    {
        return [
            'content-' . $contentId,
            'relation-' . $contentId,
        ];
    }

    public function getLocationTags(int $locationId): array
    {
        return [
            'location-' . $locationId,
            'parent-' . $locationId,
            'relation-location-' . $locationId,
        ];
    }

    public function getParentLocationTags(int $locationId): array
    {
        return [
            'location-' . $locationId,
            'parent-' . $locationId,
        ];
    }

    public function getContentLocationsTags(int $contentId): array
    {
        $tags = [];

        $locations = $this->locationHandler->loadLocationsByContent($contentId);

        foreach ($locations as $location) {
            $tags = array_merge(
                $tags,
                $this->getLocationTags($location->id),
                $this->getParentLocationTags($location->parentId),
            );
        }

        return $tags;
    }

    public function getContentUrlTags(int $urlId): array
    {
        $tags = [];

        $contentIds = $this->urlHandler->findUsages($urlId);

        foreach ($contentIds as $contentId) {
            $tags[] = 'content-' . $contentId;
        }

        return $tags;
    }
}
