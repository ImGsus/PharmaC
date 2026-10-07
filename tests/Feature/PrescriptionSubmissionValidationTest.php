<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\PrescriptionController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PrescriptionSubmissionValidationTest extends TestCase
{
    public function test_submission_requires_a_photo_and_review_confirmation()
    {
        $request = Request::create('/prescriptions', 'POST', ['review_confirmed' => '0']);

        try {
            (new PrescriptionController())->store($request);
            $this->fail('An empty prescription submission should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('review_confirmed', $exception->errors());
            $this->assertArrayHasKey('document', $exception->errors());
        }
    }

    public function test_submission_requires_review_confirmation_even_when_a_photo_is_attached()
    {
        $request = Request::create(
            '/prescriptions',
            'POST',
            [],
            [],
            ['document' => UploadedFile::fake()->create('prescription.png', 1, 'image/png')]
        );

        try {
            (new PrescriptionController())->store($request);
            $this->fail('A prescription without review confirmation should be rejected.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('review_confirmed', $exception->errors());
        }
    }
}
