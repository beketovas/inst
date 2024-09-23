<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Integration\Entities\Integration;

class IntegrationDeactivatedDueToErrors extends Mailable
{
    use Queueable, SerializesModels;

    protected Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    public function build(): IntegrationDeactivatedDueToErrors
    {
        return $this
            ->subject("Your way has been deactivated")
            ->markdown(config('app.theme').'.emails.integration.deactivated_due_to_errors', [
                'username' => $this->integration->user->name,
                'wayLink' => route('integrations.nodes', $this->integration->code),
                'wayName' => $this->integration->name ?? 'Way Name'
            ]);
    }
}
