<?php declare(strict_types=1);

/**
 * @author Stanislav Taranovskyi <staranovskyi@gmail.com>
 */

namespace Modules\Integration\Http\Controllers;

use Illuminate\Http\Response;
use Nwidart\Modules\Routing\Controller;

class BaseWebhookController extends Controller
{

    public function __construct()
    {
        if (!config('app.enable_webhooks')) {
            die('Webhooks are disabled');
        }
    }
}
