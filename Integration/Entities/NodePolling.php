<?php

namespace Modules\Integration\Entities;

use App\Traits\CacheBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Application\Entities\Application;
use Modules\Integration\Contracts\BaseNode;
use Modules\User\Entities\User;

class NodePolling extends Model implements BaseNode
{
    use CacheBuilder;

    protected $table = 'node_pollings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_type', 'node_id', 'trigger_type', 'user_id'
    ];

    /**
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function node(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'node_id');
    }

    public function trigger(): BelongsTo
    {
        $class = 'Modules\\'.studly_case($this->application_type).'\\Entities\\Action';
        return $this->belongsTo($class, 'trigger_type', 'type');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function flushCache()
    {
        $this->cacheForget(
            ['node_polling', 'all'],
            ['node_polling', 'application_'.$this->application_type],
            ['node_polling', 'application_'.$this->application_type.'_trigger_'.$this->trigger_type],
            ['node_polling', 'settings_id_'.$this->id]
        );
    }
}
