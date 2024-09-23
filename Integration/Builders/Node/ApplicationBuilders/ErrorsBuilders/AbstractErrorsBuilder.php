<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\ErrorsBuilders;

use Modules\Application\Contracts\NodeErrorsBuilderContract;
use Modules\Integration\Entities\Node as BaseNode;

abstract class AbstractErrorsBuilder implements NodeErrorsBuilderContract
{
    protected BaseNode $baseNode;

    protected array $errors = [];

    public function __construct(BaseNode $baseNode)
    {
        $this->baseNode = $baseNode;
    }

    public function addError($errorName, $errorMessage)
    {
        $this->errors[$errorName] = $errorMessage;
    }

    abstract public function build();

    public function getErrors(): array
    {
        return $this->errors;
    }
}
