<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PrescriptionController;
use Illuminate\Http\Client\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class PrescriptionGroqTest extends TestCase
{
    public function test_groq_request_uses_the_configured_vision_model_and_image()
    {
        config(['services.groq.prescription_model' => 'qwen/qwen3.8-27b']);
        $apiKey = 'test-groq-api-key';
        $file = UploadedFile::fake()->create('prescription.png', 1, 'image/png');
        $imageContents = file_get_contents($file->getRealPath());
        $capturedRequest = null;

        Http::fake(function (Request $request) use (&$capturedRequest) {
            $capturedRequest = $request;

            return Http::response([
                'error' => ['message' => 'Rate limit reached.'],
            ], 429);
        });

        $response = $this->callGroqAnalyzer($file, $apiKey);

        $this->assertSame(429, $response->getStatusCode());
        $this->assertSame('https://api.groq.com/openai/v1/chat/completions', $capturedRequest->url());
        $this->assertTrue($capturedRequest->hasHeader('Authorization', 'Bearer '.$apiKey));
        $this->assertSame('qwen/qwen3.8-27b', $capturedRequest->data()['model']);
        $this->assertSame(
            'data:image/png;base64,'.base64_encode($imageContents),
            $capturedRequest->data()['messages'][0]['content'][1]['image_url']['url']
        );
        $this->assertStringContainsString('Groq rate limit or quota reached', $response->getData(true)['message']);
    }

    private function callGroqAnalyzer(UploadedFile $file, string $apiKey): JsonResponse
    {
        $controller = new PrescriptionController();
        $method = new ReflectionMethod($controller, 'analyzePrescriptionWithGroq');
        $method->setAccessible(true);

        return $method->invoke($controller, $file, $apiKey);
    }
}
