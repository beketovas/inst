<?php declare(strict_types=1);

namespace Modules\Application\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\Application\Facades\ApplicationRepository;
use Modules\Application\Facades\ApplicationAccount;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if($this->post('action_type') == 'disconnect')
            return [];
        $slug = $this->route()->parameter('slug');
        $applicationType = ApplicationRepository::getBySlug($slug)->type;
        $settings = ApplicationAccount::getAuthConfig($applicationType)->settings;
        $rules = [];

        if(!isset($settings))
            return $rules;

        foreach ($settings as $setting) {
            $rules['fields.'.$setting['name']] = 'required';
        }
        return $rules;
    }
}
