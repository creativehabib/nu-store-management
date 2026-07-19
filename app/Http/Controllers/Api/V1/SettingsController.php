<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\ApprovalWorkflow;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $siteLogo = setting('site_logo');
        $siteFavicon = setting('site_favicon');

        return response()->json([
            'data' => [
                'site' => [
                    'name' => setting('site_name', 'Inventory Management System'),
                    'email' => setting('site_email'),
                    'phone' => setting('site_phone'),
                    'address' => setting('site_address'),
                    'logo' => $siteLogo,
                    'logo_url' => $this->publicUrl($siteLogo),
                    'favicon' => $siteFavicon,
                    'favicon_url' => $this->publicUrl($siteFavicon),
                    'social_links' => [
                        'facebook' => setting('facebook_url'),
                        'twitter' => setting('twitter_url'),
                        'instagram' => setting('instagram_url'),
                    ],
                ],
                'requisition' => [
                    'show_print_footer' => (bool) setting('show_print_footer', true),
                    'store_mode' => setting('store_mode', 'departmental'),
                    'central_store_dept_id' => $this->integerSetting('central_store_dept_id', 1),
                    'approval_flow_roles' => ApprovalWorkflow::roles(),
                ],
            ],
        ]);
    }

    protected function publicUrl(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    protected function integerSetting(string $key, int $default): int
    {
        return (int) setting($key, $default);
    }
}
