<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\Integration\Entities\Integration;
use Modules\Integration\Facades\IntegrationStorage;
use Modules\Integration\Mails\NotWorkingIntegration;

class DeactivateNotWorkingIntegration extends Command
{
    protected $signature = 'integration-deactivation-not-working {?--days=} {?--emails=}';

    protected $description = 'Send an email to a user indicating an inactive integration for more than specified days and disable it';

    public function handle()
    {
        $days = $this->option('days') ?? 90;
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
            ->with('nodes.application', 'user')
            ->get();

        $this->log($integrations);

        $integrations->each(function(Integration $integration) use ($days) {
            $integrationStorage = IntegrationStorage::load($integration);
            Log::channel('integrations')->info($integrationStorage->getIntegration()->user->email. ': '. $integration->id);
            if(config('mail.send_all_messages')) {
                Mail::to($integrationStorage->getIntegration()->user->email)
                    ->send(new NotWorkingIntegration($integrationStorage, $days));
            }
            $integrationService = app()->makeWith(IntegrationServiceContract::class, [
                'integration' => $integration
            ]);
            $integrationService->deactivateBy('api', getCommandName());
        });

        $this->info('Total non-working integrations: '.$integrations->count());
    }

    protected function log(Collection $integrations)
    {
        if($integrations->count() > 0) {
            Log::channel('integrations')->info(__('site.log_section_separator'));
            Log::channel('integrations')->info('Integrations deactivated');
            Log::channel('integrations')->info('Command: '.getCommandName());
            Log::channel('integrations')->info('Integrations: ');
        }
    }
}
