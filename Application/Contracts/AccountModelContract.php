<?php
namespace Modules\Application\Contracts;

/**
 * Interface AccountModelContract
 * @package Modules\Application\Contracts
 *
 * @property string $api_key
 * @property string $access_token
 * @property string $application_type
 */
interface AccountModelContract
{
    public function application();

}
