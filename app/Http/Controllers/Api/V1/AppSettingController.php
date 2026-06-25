<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    /**
     * Get all app settings
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $settings = AppSetting::all()->mapWithKeys(function ($setting) {
                return [$setting->key => [
                    'value' => $this->convertValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'group' => $setting->group,
                ]];
            });

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific setting by key
     *
     * @param string $key
     * @return JsonResponse
     */
    public function show(string $key): JsonResponse
    {
        try {
            $setting = AppSetting::where('key', $key)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètre non trouvé',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $setting->key,
                    'value' => $this->convertValue($setting->value, $setting->type),
                    'type' => $setting->type,
                    'group' => $setting->group,
                    'description' => $setting->description,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du paramètre',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get settings by group
     *
     * @param string $group
     * @return JsonResponse
     */
    public function getByGroup(string $group): JsonResponse
    {
        try {
            $settings = AppSetting::where('group', $group)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->key => [
                        'value' => $this->convertValue($setting->value, $setting->type),
                        'type' => $setting->type,
                    ]];
                });

            return response()->json([
                'success' => true,
                'data' => $settings,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des paramètres du groupe',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get legal links (privacy policy, terms of service)
     *
     * @return JsonResponse
     */
    public function getLegalLinks(): JsonResponse
    {
        try {
            $privacyPolicyUrl = AppSetting::get('privacy_policy_url', '');
            $termsOfServiceUrl = AppSetting::get('terms_of_service_url', '');

            return response()->json([
                'success' => true,
                'data' => [
                    'privacy_policy_url' => $privacyPolicyUrl,
                    'terms_of_service_url' => $termsOfServiceUrl,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des liens légaux',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a setting (admin only - to be secured with middleware)
     *
     * @param Request $request
     * @param string $key
     * @return JsonResponse
     */
    public function update(Request $request, string $key): JsonResponse
    {
        try {
            $validated = $request->validate([
                'value' => 'required|string',
                'type' => 'sometimes|string|in:string,url,json,boolean,number',
                'group' => 'sometimes|string',
                'description' => 'sometimes|string',
            ]);

            $setting = AppSetting::where('key', $key)->first();

            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paramètre non trouvé',
                ], 404);
            }

            $setting->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Paramètre mis à jour avec succès',
                'data' => $setting,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du paramètre',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Convert value based on type
     *
     * @param string $value
     * @param string $type
     * @return mixed
     */
    private function convertValue($value, string $type)
    {
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float)$value : $value;
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}
