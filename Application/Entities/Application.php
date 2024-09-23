<?php

namespace Modules\Application\Entities;

use Apiway\Auth\Oauth2\Traits\ConfigHelper;
use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\ContentManagement\Entities\Softs\Soft;

class Application extends Model
{
    use CacheBuilder, ConfigHelper;

    protected $fillable = ['id', 'name', 'slug', 'icon', 'default_icon', 'beta', 'type', 'active', 'development', 'created_at', 'updated_at'];

    /**
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany('Modules\User\Entities\User')->withTimestamps();
    }

    /**
     * If applications belongs to user or not
     *
     * @param int $userId
     * @return bool
     */
    public function belongsToUser($userId)
    {
        if($this->users()->where('user_id', $userId)) {
            return true;
        }
        return false;
    }

    /**
     * @return BelongsToMany
     */
    public function integrations()
    {
        return $this->belongsToMany('App\Models\Integration')->withTimestamps();
    }

    public function soft()
    {
        return $this->hasOne(Soft::class);
    }

    /**
     * @return HasMany
     */
    public function fields()
    {
        return $this->hasMany('App\Models\Application\Field');
    }

    /**
     * @return bool
     */
    public function hasAccount()
    {
        $authConfig = $this->getConfig($this->type, 'auth');
        if(!isset($authConfig) || $authConfig['type'] === 'default') {
            return false;
        }
        return true;
    }

    /**
     * Automatically loading model by application slug
     *
     * @param int $userId
     * @return {Application}\Entities\Account
     */
    public function account($userId)
    {
        $modelName = "Modules\\".studly_case($this->type)."\\Entities\\Account";
        if(!class_exists($modelName)) {
            return Account::where('user_id', $userId)->where('application_type', $this->type)->first();
        }
        return $modelName::where('user_id', $userId)->first();
    }

    /**
     * Automatically loading model by application type
     *
     * @return {Application}\Entities\Action|null
     */
    public function actions()
    {
        $modelName = "Modules\\".studly_case($this->type)."\\Entities\\Action";
        if(!class_exists($modelName)) {
            return null;
        }
        $actions = $modelName::get();
        return count($actions) > 0 ? $actions : null;
    }

    public function getDefaultIconUrlAttribute()
    {
        return '/uploads/services-icons/'.$this->default_icon;
    }

    public function getIconUrlAttribute()
    {
        return $this->icon ? Storage::url($this->icon) : $this->defaultIconUrl;
    }

    public function flushCache()
    {
        $this->cacheFlushTags('application_'.$this->type);
        $this->cacheForget(['applications', $this->type]);
        $this->cacheForget(['applications', 'slug_'.$this->slug]);
    }
}
