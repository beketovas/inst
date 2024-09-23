<?php
namespace Modules\Integration\Builders;

use Apiway\ServicesDataStorage\Contracts\ElementInterface;
use Apiway\ServicesDataStorage\Contracts\ElementBuilderInterface;
use Apiway\ServicesDataStorage\ElementBuilder;
use Modules\Integration\Entities\DataElement;
use Modules\LeeLoo\Helpers\FieldHelper;


class DataElementBuilder extends ElementBuilder implements ElementBuilderInterface
{
    protected static function titles()
    {
        return [
            'email' => __('application::fields.email'),
            'phone' => __('application::fields.phone'),
            'first_name' => __('application::fields.first_name'),
        ];
    }

    public function addTitle(string $title)
    {
        $title = FieldHelper::titleFromSystemName($title);
        $this->element->setTitle($title);
    }

    public function __construct()
    {
        $this->element = new DataElement();
    }

    public function addBaseTitle(string $fieldName)
    {
        if(isset(self::titles()[$fieldName])) {
            $this->element->setTitle(self::titles()[$fieldName]);
        } else {
            $this->element->setTitle($fieldName);
        }
    }

    public function addType(string $type)
    {
        $this->element->setType($type);
    }

    public function addRequired(bool $required)
    {
        $this->element->setRequired($required);
    }

    public function addDynamic(bool $dynamic)
    {
        $this->element->setDynamic($dynamic);
    }

    public function addUsesFields(string $usesFields)
    {
        $this->element->setUsesFields($usesFields);
    }

    public function addDropdownSource(string $dropdownSource)
    {
        $this->element->setDropdownSource($dropdownSource);
    }

    public function addDescription(string $description)
    {
        $this->element->setDescription($description);
    }

    public function addPosition(string $position)
    {
        $this->element->setPosition($position);
    }

    public function addCustomField(bool $customField)
    {
        $this->element->setCustomField($customField);
    }

    public function addLoader(string $loader)
    {
        $this->element->setLoader($loader);
    }

    public function addOrdering(int $ordering)
    {
        $this->element->setOrdering($ordering);
    }

    public function addParentId(int $parentId)
    {
        $this->element->setParentId($parentId);
    }

    //for some applications(Amo, Unisender, ActiveCampaign)
    public function addLogicalRequired()
    {
        if (in_array($this->element->getRole(), config('unisender.contact.required'))) {
            $this->element->setRequired(true);
        } else {
            $this->element->setRequired(false);
        }
    }

    public function addIsCustomField(bool $isCustomField)
    {
        $this->element->setIsCustomField($isCustomField);
    }

    public function addFieldName(string $fieldName)
    {
        $this->element->setSystemName($fieldName);
    }

    public function addIsVisible(bool $isVisible)
    {
        $this->element->setIsVisible($isVisible);
    }

    public function addViewPos(int $viewPos)
    {
        $this->element->setViewPos($viewPos);
    }

    public function addRole(string $role)
    {
        $this->element->setRole($role);
    }

    public function addFieldId(int $fieldId)
    {
        $this->element->setSystemId($fieldId);
    }

    public function addEnumId(int $enumId)
    {
        $this->element->setEnumId($enumId);
    }

    public function addPlaceholder(string $placeholder)
    {
        $this->element->setPlaceholder($placeholder);
    }

    public function addMask(string $mask)
    {
        $this->element->setMask($mask);
    }

    public function addBaseIdentifier(string $fieldName)
    {
        $identifier = $fieldName;
        if($this->element->contact)
            $identifier = 'contact_'.$identifier;
        else if($this->element->lead)
            $identifier = 'lead_'.$identifier;

        $this->element->setIdentifier($identifier);
    }

    public function addContact(bool $contact)
    {
        $this->element->setContact($contact);
    }

    public function addLead(bool $lead)
    {
        $this->element->setLead($lead);
    }

    public function addApiwayPipelineBaseTitle(string $fieldName, string $preText = '')
    {
        $title = self::titleFromSystemName($fieldName);
        if($preText)
            $title = $preText.' '.$title;

        $this->element->setTitle($title);
    }

    public static function titleFromSystemName($systemName)
    {
        $titleArr = explode('_', $systemName);

        $titles = [];
        foreach ($titleArr as $title) {
            if(empty($title)) continue;

            $titles[] = ucfirst($title);
        }
        $titlesStr = implode(' ', $titles);
        $titlesStr = trim($titlesStr);
        return $titlesStr;
    }

    public function addCode(string $code)
    {
        $this->element->setCode($code);
    }

    public function addCustomIdentifier(int $fieldId, int $enumId = 0)
    {
        $identifier = '';
        if($this->element->contact)
            $identifier = 'contact_custom_'.$fieldId;
        else if($this->element->lead)
            $identifier = 'lead_custom_'.$fieldId;

        if($enumId)
            $identifier .= '_'.$enumId;

        $this->element->setIdentifier($identifier);
    }

    public function addApiwayPipelineTitle(string $fieldName, string $preText = '')
    {
        $title = $fieldName;
        if($preText)
            $title = $preText.' '.$fieldName;

        $this->element->setTitle($title);
    }

    ////////////////////////////////////////////////

    /**
     * @return DataElement
     */
    public function getElement(): ElementInterface
    {
        return $this->element;
    }
}
