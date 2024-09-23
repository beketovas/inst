<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Integration\Storage\Integration;

class IntegrationDeactivatedDueToSubscription extends Mailable
{
    use Queueable, SerializesModels;

    protected Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    public function build(): IntegrationDeactivatedDueToSubscription
    {
        return $this
            ->subject("Your way has been deactivated")
            ->markdown(config('app.theme').'.emails.integration.deactivated_due_to_subscription', [
                'username' => $this->integration->getIntegration()->user->name,
                'wayEditLink' => route('integrations.nodes', $this->integration->getIntegration()->code),
                'wayName' => $this->integration->getIntegration()->name ?? 'Way Name'
            ]);
    }
}
