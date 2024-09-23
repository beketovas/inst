<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Integration\Storage\Integration;

class IntegrationDeactivatedDueToIncomingData extends Mailable
{
    use Queueable, SerializesModels;

    protected Integration $integration;

    protected int $days;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    public function build(): IntegrationDeactivatedDueToIncomingData
    {
        return $this
            ->subject("Your way has been deactivated")
            ->markdown(config('app.theme').'.emails.integration.deactivated_due_to_incoming_data', [
                'username' => $this->integration->getIntegration()->user->name,
                'wayEditLink' => route('integrations.nodes', $this->integration->getIntegration()->code),
                'wayName' => $this->integration->getIntegration()->name ?? 'Way Name',
            ]);
    }
}
