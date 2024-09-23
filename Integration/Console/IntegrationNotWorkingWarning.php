<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Mails\WarningIntegration;

class IntegrationNotWorkingWarning extends Command
{
    protected $signature = 'integrations-not-working-warning {?--days=} {?--emails=}';

    protected $description = 'Send an email to a user with a warning about inactive integrations for more than 1 day';

    public function handle()
    {
        $days = $this->option('days') ?? 1;
        $emails = $this->option('emails');
        $emailsArray = [];
        if(isset($emails))
            $emailsArray = explode(',', $emails);

        $integrations = Integration::query()
            ->select('integrations.*')
            ->from('integrations')
            ->whereNotIn('integrations.id', function(Builder $where) use ($days) {
                $where->select('id.integration_id')
                    ->from('incoming_data as id')
                    ->where('id.integration_id', '=', DB::raw('integrations.id'))
                    ->whereBetween('id.created_at', [DB::raw("date_sub(now(), INTERVAL {$days} DAY)"), DB::raw("now()")]);
            })
            ->when(isset($emails), function ($when) use ($emailsArray) {
                $when->join('users as u', 'u.id', '=', 'integrations.user_id')
                    ->whereIn('u.email', $emailsArray);
            })
            ->where('integrations.updated_at', '<', DB::raw("date_sub(now(), INTERVAL {$days} DAY)"))
            ->where('integrations.active', true)
            ->where('warned', false)
            ->with('nodes.application', 'user')
            ->get();

        $this->log($integrations);

        $integrations->each(function(Integration $integration) {
            //Mail::to($integration->user->email)
            //    ->send(new WarningIntegration($integration));
            Log::channel('integrations')->info($integration->user->email. ': '. $integration->id);
            $integration->warned = true;
            $integration->save();
            $integration->flushCache();
        });

        $this->info('Total integrations: '.$integrations->count());
    }

    protected function log(Collection $integrations)
    {
        if($integrations->count() > 0) {
            Log::channel('integrations')->info(__('site.log_section_separator'));
            Log::channel('integrations')->info('Users warned about not working integrations');
            $arrayCommand = $_SERVER['argv'];
            unset($arrayCommand[0]);
            $command = implode(' ', $arrayCommand);
            Log::channel('integrations')->info('Command: '.$command);
            Log::channel('integrations')->info('Integrations: ');
        }
    }
}
