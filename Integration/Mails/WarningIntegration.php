<?php

namespace Modules\Integration\Mails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Integration\Entities\Integration;

class WarningIntegration extends Mailable
{
    use Queueable, SerializesModels;

    protected Integration $integration;

    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    public function build(): WarningIntegration
    {
        return $this
            ->subject("Check your Way")
            ->markdown(config('app.theme').'.emails.integration.warning', [
                'username' => $this->integration->user->name,
                'wayEditLink' => route('integrations.nodes', $this->integration->code),
                'wayName' => $this->integration->name ?? 'Way Name'
            ]);
    }
}
