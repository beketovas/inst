<?php declare(strict_types=1);

namespace Modules\Application\Contracts;

use Modules\Application\Builders\RequestUrlBuilder;

interface Saver
{
    public function save(string $slug, ?array $fields = []): RequestUrlBuilder;
}
