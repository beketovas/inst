<?php

namespace Modules\Integration\Entities;

class   DynamicField
{
    protected string $label;
    protected $value;
    protected array $additional_data;

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return array
     */
    public function getAdditionalData(): array
    {
        return $this->additional_data;
    }

    /**
     * @param array $additional_data
     */
    public function setAdditionalData(array $additional_data): void
    {
        $this->additional_data = $additional_data;
    }
}
