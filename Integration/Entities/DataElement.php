<?php
namespace Modules\Integration\Entities;

use Apiway\ServicesDataStorage\Element;
use Apiway\ServicesDataStorage\Contracts\ElementInterface;

class DataElement extends Element implements ElementInterface
{
    /**
     * @description Common properties
     */
    public $ordering = 0;
    public $uses_fields = null;
    public $dropdown_source = null;
    public $dynamic = 0;
    public $required = 0;
    public $type = 'string';
    public $description = '';
    public $position;
    public $custom_field = false;
    public $loader;
    public $parentId;

    /**
     * @description Custom properties
     */
    public $system_id;
    public $system_name;
    public $is_custom_field;
    public $role;
    public $is_visible = 1;
    public $view_pos = 1;
    public $enum_id;
    public $mask = '';
    public $placeholder = '';
    public $contact;
    public $lead;
    public $code;

    /**
     * @param string $type
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->required = $required;
    }

    /**
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * @param bool $dynamic
     */
    public function setDynamic(bool $dynamic)
    {
        $this->dynamic = $dynamic;
    }

    /**
     * @return bool
     */
    public function getDynamic()
    {
        return $this->dynamic;
    }

    /**
     * @param string $usesFields
     */
    public function setUsesFields(string $usesFields)
    {
        $this->uses_fields = $usesFields;
    }

    /**
     * @return string
     */
    public function getUsesFields()
    {
        return $this->uses_fields;
    }

    /**
     * @param string $dropdownSource
     */
    public function setDropdownSource(string $dropdownSource)
    {
        $this->dropdown_source = $dropdownSource;
    }

    /**
     * @return string
     */
    public function getDropdownSource()
    {
        return $this->dropdown_source;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $position
     */
    public function setPosition(string $position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param bool $customField
     */
    public function setCustomField(bool $customField)
    {
        $this->custom_field = $customField;
    }

    /**
     * @return string
     */
    public function getCustomField()
    {
        return $this->custom_field;
    }

    /**
     * @param string $loader
     */
    public function setLoader(string $loader)
    {
        $this->loader = $loader;
    }

    /**
     * @return string
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * @param int $ordering
     */
    public function setOrdering(int $ordering)
    {
        $this->ordering = $ordering;
    }

    /**
     * @return int
     */
    public function getOrdering()
    {
        return $this->ordering;
    }

    /**
     * @param int $parentId
     */
    public function setParentId(int $parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param string $systemName
     */
    public function setSystemName(string $systemName)
    {
        $this->system_name = $systemName;
    }

    /**
     * @return string
     */
    public function getSystemName()
    {
        return $this->system_name;
    }

    /**
     * @param bool $isCustomField
     */
    public function setIsCustomField(bool $isCustomField)
    {
        $this->is_custom_field = $isCustomField;
    }

    /**
     * @return bool
     */
    public function getIsCustomField()
    {
        return $this->custom_field;
    }

    /**
     * @return string
     */
    public function getIsVisible()
    {
        return $this->is_visible;
    }

    /**
     * @param bool $isVisible
     */
    public function setIsVisible(bool $isVisible)
    {
        $this->is_visible = $isVisible;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role)
    {
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * @return int
     */
    public function getViewPos()
    {
        return $this->view_pos;
    }

    /**
     * @param int $viewPos
     */
    public function setViewPos(int $viewPos)
    {
        $this->view_pos = $viewPos;
    }

    /**
     * @param int $systemId
     */
    public function setSystemId(int $systemId)
    {
        $this->system_id = $systemId;
    }

    /**
     * @return int
     */
    public function getSystemId()
    {
        return $this->system_id;
    }

    /**
     * @param int $enumId
     */
    public function setEnumId(int $enumId)
    {
        $this->enum_id = $enumId;
    }

    /**
     * @return int
     */
    public function getEnumId()
    {
        return $this->enum_id;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder(string $placeholder)
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * @param string $mask
     */
    public function setMask(string $mask)
    {
        $this->mask = $mask;
    }

    /**
     * @return string
     */
    public function getMask()
    {
        return $this->mask;
    }

    /**
     * @param bool $contact
     */
    public function setContact(bool $contact)
    {
        $this->contact = $contact;
    }

    /**
     * @return bool
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param bool $lead
     */
    public function setLead(bool $lead)
    {
        $this->lead = $lead;
    }

    /**
     * @return bool
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

}
