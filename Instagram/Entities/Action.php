<?php

namespace Modules\Instagram\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Localization;

class Action extends Model
{
    use Localization;

    const TABLE_NAME = 'instagram_actions';

    protected $table = self::TABLE_NAME;

    /**
     * @return string
     */
    public function getNameLocaleAttribute()
    {
        $fieldName = $this->fieldLocale('name');

        return $this->$fieldName;
    }
}
