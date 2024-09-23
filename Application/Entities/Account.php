<?php

namespace Modules\Application\Entities;

use Apiway\ArrayManipulator\Arr;
use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Modules\Application\Contracts\AccountModelContract;
use Modules\User\Entities\User;
use Modules\Integration\Entities\Node;

class Account extends Model implements AccountModelContract
{
    use CacheBuilder;

    protected $table = 'application_accounts';

    protected $fillable = ['user_id', 'application_type', 'access_token', 'expires_in', 'refresh_token', 'account_data_json'];

    public const FIELDS = ['user_id', 'application_type', 'access_token', 'expires_in', 'refresh_token', 'account_data_json'];

    /**
     * @return \Illuminate\Database\Eloquent\Collection|Model|BelongsTo|object
     */
    public function application()
    {
        return $this->belongsTo(Application::class, 'application_type', 'type')->first();
    }

    /**
     * @return Collection
     */
    public function nodes()
    {
        return Node::where(['application_type' => $this->application_type, 'account_id' => $this->id])->get();
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fields()
    {
        $fieldsAll = $this->hasMany('App\Models\Application\Field', 'application_account_id', 'id');
        return $fieldsAll->where('application_id', $this->application()->id);
    }

    /**
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fieldsUsingType(string $type)
    {
        if(empty($type)) {
            return $this->fields();
        }
        return $this->fields()->where('type', $type);
    }

    /**
     * @return Account
     */
    public function makeAuthorized()
    {
        $this->authorized = 1;
        $this->save();
        return $this;
    }

    /**
     * @return Account
     */
    public function makeUnauthorized()
    {
        $this->authorized = 0;
        $this->save();
        return $this;
    }

    /**
     * Remove all account cache
     */
    public function flushCache()
    {
        $this->cacheForget(
            ['user_data_'.$this->user_id, 'application_account_'.$this->application_type],
            ['user_data_'.$this->user_id, 'application_account_id_'.$this->id],
        );
        $nodes = $this->nodes();
        if($nodes->isNotEmpty())
            foreach ($nodes as $node)
                $node->flushCache();
    }

    public function getJsonDataAttribute(): array
    {
        if (empty($this->account_data_json)) {
            return [];
        }

        return Arr::flatten(json_decode($this->account_data_json, true));
    }

    public function __get($key) {
        $value = $this->getAttribute($key);
        if(isset($value))
            return $value;

        return isset($this->getTransformingToArrayData()[$key]) ? $this->getTransformingToArrayData()[$key] : null;
    }

    public function getTransformingToArrayData(): array
    {
        $multiArray = $this->toArray();
        $multiArray['account_data_json'] = $this->json_data;
        return Arr::flatten($multiArray);
    }
}
