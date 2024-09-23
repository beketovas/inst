<?php

namespace Modules\Integration\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Integration\Mails\NotWorkingIntegrations;
use Modules\User\Entities\User;

class DeactivateNotWorkingIntegrations extends Command
{
    protected $signature = 'integrations-deactivation-not-working {?--days=} {?--emails=}';

    protected $description = 'Send an email to a user with a warning about inactive integrations for more than specified days and disable them';

    public function handle()
    {
        $days = $this->option('days') ?? 90;
        $emails = $this->option('emails');
        $emailsArray = [];
        if(isset($emails))
            $emailsArray = explode(',', $emails);

        $integrationQueryBuilderClosure = function($integrations) use ($days) {
            $integrations
                ->select('integrations.*')
                ->from('integrations')
                ->whereNotIn('integrations.id', function(Builder $where) use ($days) {
                    $where->select('id.integration_id')
                        ->from('incoming_data as id')
                        ->where('id.integration_id', '=', DB::raw('integrations.id'))
                        ->whereBetween('id.created_at', [DB::raw("date_sub(now(), INTERVAL {$days} DAY)"), DB::raw("now()")]);
                })
                ->where('integrations.updated_at', '<', DB::raw("date_sub(now(), INTERVAL {$days} DAY)"))
                ->where('integrations.active', true);
        };

        $usersWithUnusedIntegrations = User::query()
            ->whereHas('integrations', $integrationQueryBuilderClosure)
            ->when(isset($emails), function ($when) use ($emailsArray) {
                $when->whereIn('email', $emailsArray);
            })
            ->with(['integrations' => $integrationQueryBuilderClosure])
            ->get();

        $this->log($usersWithUnusedIntegrations);

        foreach ($usersWithUnusedIntegrations as $user) {
            $this->info('User: '.$user->email. '. Total integrations: '. $user->integrations->count());
            if(config('mail.send_all_messages')) {
                Mail::to($user->email)
                    ->send(new NotWorkingIntegrations($user, $days));
            }
        }
    }

    protected function log(Collection $users)
    {
        if($users->count() > 0) {
            Log::channel('integrations')->info(__('site.log_section_separator'));
            Log::channel('integrations')->info('Integrations deactivated');
            Log::channel('integrations')->info('Command: '.getCommandName());
            Log::channel('integrations')->info('Integrations: ');
        }
    }
}
