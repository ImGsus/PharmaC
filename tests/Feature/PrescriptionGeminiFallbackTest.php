<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PrescriptionController;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class PrescriptionGeminiFallbackTest extends TestCase
{
    public function test_default_primary_model_is_gemini_2_5_flash()
    {
        $this->assertSame('gemini-2.5-flash', config('services.gemini.prescription_model'));
    }

    public function test_default_fallback_model_is_a_current_gemini_flash_model()
    {
        $this->assertSame('gemini-3.7-flash', config('services.gemini.prescription_fallback_model'));
    }

    public function test_it_uses_the_fallback_when_the_primary_model_is_unavailable_to_the_key()
    {
        config([
            'services.gemini.prescription_model' => 'gemini-2.5-flash',
            'services.gemini.prescription_fallback_model' => 'gemini-3.7-flash',
        ]);
        $models = [];

        Http::fake(function (Request $request) use (&$models) {
            preg_match('#/models/([^:]+):generateContent$#', $request->url(), $matches);
            $models[] = $matches[1] ?? null;

            if (count($models) === 1) {
                return Http::response([
                    'error' => ['message' => 'This model is no longer available to new users.'],
                ], 404);
            }

            return Http::response([
                'error' => ['message' => 'This model is currently experiencing high demand.'],
            ], 503);
        });

        $response = $this->callGeminiAnalyzer();

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(['gemini-2.5-flash', 'gemini-3.7-flash'], $models);
        $this->assertStringContainsString('trying the backup model', $response->getData(true)['message']);
    }

    public function test_it_retries_a_503_then_tries_the_configured_fallback_model()
    {
        config([
            'services.gemini.prescription_model' => 'gemini-primary',
            'services.gemini.prescription_fallback_model' => 'gemini-fallback',
        ]);
        $models = [];

        Http::fake(function (Request $request) use (&$models) {
            preg_match('#/models/([^:]+):generateContent$#', $request->url(), $matches);
            $models[] = $matches[1] ?? null;

            return Http::response([
                'error' => [
                    'message' => 'This model is currently experiencing high demand.',
                    'status' => 'UNAVAILABLE',
                ],
            ], 503);
        });

        $response = $this->callGeminiAnalyzer();

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame([
            'gemini-primary',
            'gemini-primary',
            'gemini-fallback',
        ], $models);
        $this->assertStringContainsString('trying the backup model', $response->getData(true)['message']);
    }

    public function test_it_does_not_try_a_fallback_model_for_quota_errors()
    {
        config([
            'services.gemini.prescription_model' => 'gemini-primary',
            'services.gemini.prescription_fallback_model' => 'gemini-fallback',
        ]);
        $models = [];

        Http::fake(function (Request $request) use (&$models) {
            preg_match('#/models/([^:]+):generateContent$#', $request->url(), $matches);
            $models[] = $matches[1] ?? null;

            return Http::response([
                'error' => ['message' => 'Quota exceeded.'],
            ], 429);
        });

        $response = $this->callGeminiAnalyzer();

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame(['gemini-primary'], $models);
    }

    private function callGeminiAnalyzer(): JsonResponse
    {
        $controller = new PrescriptionController();
        $method = new ReflectionMethod($controller, 'analyzePrescriptionWithGemini');
        $method->setAccessible(true);

        return $method->invoke(
            $controller,
            UploadedFile::fake()->create('prescription.png', 1, 'image/png'),
            'test-api-key'
        );
    }
}
