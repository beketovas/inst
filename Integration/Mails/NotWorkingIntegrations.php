<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Integration\Contracts\IntegrationServiceContract;
use Modules\User\Entities\User;

class NotWorkingIntegrations extends Mailable
{
    use Queueable, SerializesModels;

    protected User $user;

    protected int $days;

    public function __construct(User $user, int $days)
    {
        $this->user = $user;
        $this->days = $days;
    }

    public function build(): NotWorkingIntegrations
    {
        $ids = '';
        $ways = '';
        $i = 0;
        foreach ($this->user->integrations as $integration) {
            $integrationService = app()->makeWith(IntegrationServiceContract::class, [
                'integration' => $integration
            ]);
            if($i != 0) {
                $ways .= ', ';
                $ids .= ', ';
            }
            $wayName = $integration->name ?? 'Way Name';
            $ways.= '&quot;<a href="'.route('integrations.nodes', $integration->code).'"><strong>'.$wayName.'</strong></a>&quot;';
            $ids .= $integration->id;
            $integrationService->deactivateBy('api', getCommandName());
            ++$i;
        }
        Log::channel('integrations')->info($this->user->email.': ('.$ids.')');
        return $this
            ->subject("Your ways have been deactivated")
            ->markdown(config('app.theme').'.emails.integration.not_working_bulk', [
                'username' => $this->user->name,
                'ways' => $ways,
                'days' => $this->days
            ]);
    }
}
