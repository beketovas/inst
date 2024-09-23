<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Integration\Storage\Integration;

class NotWorkingIntegration extends Mailable
{
    use Queueable, SerializesModels;

    protected Integration $integration;

    protected int $days;

    public function __construct(Integration $integration, int $days)
    {
        $this->integration = $integration;
        $this->days = $days;
    }

    public function build(): NotWorkingIntegration
    {
        $user = $this->integration->getIntegration()->user;
        $triggerApp = $this->integration->triggerNodeApplication;
        $actionApp = $this->integration->actionNodeApplication;
        $triggerAccount = ApplicationRepository::getApplicationAccount($triggerApp, $user->id);
        $actionAccount = ApplicationRepository::getApplicationAccount($actionApp, $user->id);

        return $this
            ->subject("Your way has been deactivated")
            ->markdown(config('app.theme').'.emails.integration.not_working', [
                'username' => $this->integration->getIntegration()->user->name,
                'wayEditLink' => route('integrations.nodes', $this->integration->getIntegration()->code),
                'wayName' => $this->integration->getIntegration()->name ?? 'Way Name',
                'triggerAppName' => $triggerApp->name,
                'actionAppName' => $actionApp->name,
                'editLinkTriggerApp' => $triggerAccount ? route('application.edit', ['slug' => $triggerApp->slug, 'id' => $triggerAccount->id]) : route('application.create', ['slug' => $triggerApp->slug]),
                'editLinkActionApp' => $actionAccount ? route('application.edit', ['slug' => $actionApp->slug, 'id' => $actionAccount->id]) : route('application.create', ['slug' => $actionApp]),
                'days' => $this->days,
            ]);
    }
}
