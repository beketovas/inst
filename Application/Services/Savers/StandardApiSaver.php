<?php declare(strict_types=1);

namespace Modules\Application\Services\Savers;

use Modules\Application\Builders\RequestUrlBuilder;
use Modules\Application\Contracts\Saver;
use Modules\Application\Facades\ApplicationAccount;
use Modules\Application\Facades\ApplicationRepository;

class StandardApiSaver implements Saver
{
    protected RequestUrlBuilder $requestUrlBuilder;

    public function __construct(RequestUrlBuilder $requestUrlBuilder)
    {
        $this->requestUrlBuilder = $requestUrlBuilder;
    }

    public function save(string $slug, ?array $fields = []): RequestUrlBuilder
    {
        $application = ApplicationRepository::getBySlug($slug);
        $accountRepository = ApplicationAccount::getApplicationAccountRepository($application->type);

        $fields['account_data_json'] = json_encode($fields);
        if (!isset($fields['application_type'])) {
            $fields['application_type'] = $application->type;
        }

        $account = $accountRepository->store($fields, auth()->user()->id);
        $account->user->flushCache();

        $this->requestUrlBuilder->addUrl(route('application.edit', [$slug, $account->id]));

        return $this->requestUrlBuilder;
    }
}
