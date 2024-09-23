<?php

namespace Modules\Integration\Helpers;

use Modules\Integration\Entities\Integration;

class IntegrationHelper
{
    /**
     * @param Integration $integration
     * @return string
     */
    public static function getTitle(Integration $integration)
    {
        return $integration->name ?: __('integration::site.no_title');
    }
}