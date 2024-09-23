<?php

namespace Modules\Application\Contracts;

interface AccountRepository
{
    public function getById(int $id);
    public function delete(AccountModelContract $account);
}
