<?php
namespace Modules\Integration\Helpers;

class NodeExtended
{

    public $id;

    public $integration_id;

    public $application_id;

    public $application_type;

    public $application_account_id;

    public $action_id;

    public $name;

    public $ordering;

    public $isTrigger = false;

    public $application = null;

    public $availableApplications = null;

    public $action = null;

    public $availableActions = null;

    public $hasSettings = false;

    public $settings = null;

    public $availableSettings = null;

    public $showFields = false;

    public $fields = [];

    public $errors = [];

    public function __construct()
    {

    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getIntegrationId()
    {
        return $this->integration_id;
    }

    public function setIntegrationId($integrationId)
    {
        $this->integration_id = $integrationId;
    }

    public function getApplicationId()
    {
        return $this->application_id;
    }

    public function setApplicationId($applicationId)
    {
        $this->application_id = $applicationId;
    }

    public function getApplicationType()
    {
        return $this->application_type;
    }

    public function setApplicationType($applicationType)
    {
        $this->application_type = $applicationType;
    }

    public function getApplicationAccountId()
    {
        return $this->application_account_id;
    }

    public function setApplicationAccountId($applicationAccountId)
    {
        $this->application_account_id = $applicationAccountId;
    }

    public function getActionId()
    {
        return $this->action_id;
    }

    public function setActionId($actionId)
    {
        $this->action_id = $actionId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getOrdering()
    {
        return $this->ordering;
    }

    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    }

    public function getIsTrigger()
    {
        return $this->isTrigger;
    }

    public function setIsTrigger($isTrigger)
    {
        $this->isTrigger = $isTrigger;
    }

    public function getApplication()
    {
        return $this->application;
    }

    public function setApplication($application)
    {
        $this->application = $application;
    }

    public function getAvailableApplications()
    {
        return $this->availableApplications;
    }

    public function setAvailableApplications($availableApplications)
    {
        $this->availableApplications = $availableApplications;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function setAction($action)
    {
        $this->action = $action;
    }

    public function getAvailableActions()
    {
        return $this->availableActions;
    }

    public function setAvailableActions($availableActions)
    {
        $this->availableActions = $availableActions;
    }

    public function getHasSettings()
    {
        return $this->hasSettings;
    }

    public function setHasSettings($hasSettings)
    {
        $this->hasSettings = $hasSettings;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getAvailableSettings()
    {
        return $this->availableSettings;
    }

    public function setAvailableSettings($availableSettings)
    {
        $this->availableSettings = $availableSettings;
    }

    public function getShowFields()
    {
        return $this->showFields;
    }

    public function setShowFields($showFields)
    {
        $this->showFields = $showFields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields($fields)
    {
        $this->fields = $fields;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    public function addError(array $error)
    {
        $this->errors = array_merge($this->errors, $error);
    }

    public function removeError($errorKey)
    {
        unset($this->errors[$errorKey]);
    }


}
