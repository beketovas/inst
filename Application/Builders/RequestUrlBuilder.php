<?php declare(strict_types=1);

namespace Modules\Application\Builders;

use Symfony\Component\HttpFoundation\Cookie;

class RequestUrlBuilder
{
    private string $url;

    private array $cookie;

    public function __construct()
    {
        $this->url = '';
    }

    public function addUrl(string $url)
    {
        $this->url = $url;
    }

    public function addCookie(Cookie $cookie)
    {
        $this->cookie[] = $cookie;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getCookie(): ?array
    {
        return $this->cookie ?? [];
    }
}
