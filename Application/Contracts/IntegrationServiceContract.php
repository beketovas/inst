<?php

namespace Modules\Application\Contracts;

interface IntegrationServiceContract
{
    public function activate();

    public function deactivate();

}