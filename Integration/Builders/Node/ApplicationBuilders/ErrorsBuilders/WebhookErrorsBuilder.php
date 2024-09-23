<?php

namespace Modules\Integration\Builders\Node\ApplicationBuilders\ErrorsBuilders;

class WebhookErrorsBuilder extends AbstractErrorsBuilder
{
    /**
     * Build errors for node
     */
    public function build()
    {
        $applicationNode = $this->baseNode->applicationNode;

        // If no fields
        $fields = $applicationNode->nodeFields;
        if($fields instanceof \Countable && !count($fields)) {
            $this->addError('fields_are_empty', __('validation.no_available_fields'));
        }
    }
}
