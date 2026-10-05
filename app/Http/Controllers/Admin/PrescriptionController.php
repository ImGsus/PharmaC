<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GeminiApiCredential;
use App\Models\Prescription;
use App\Models\Product;
use App\Services\OrganizedFileStorage;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Exception\RuntimeException as ProcessRuntimeException;
use Symfony\Component\Process\Process;
use Yajra\DataTables\DataTables;

class PrescriptionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $prescriptions = Prescription::query();
            // Keep newest-first as the default until the user sorts a column,
            // otherwise the base latest() would neutralize the requested sort.
            if (! $request->filled('order')) {
                $prescriptions->latest();
            }

            return DataTables::of($prescriptions)
                ->editColumn('created_at', function ($prescription) {
                    return optional($prescription->created_at)->format('d M Y');
                })
                ->editColumn('patient_name', function ($prescription) {
                    $details = $prescription->ocr_details ?: [];
                    $filename = $prescription->document_path ? basename($prescription->document_path) : null;
                    $documentUrl = $filename ? url('storage/system/prescriptions/'.$filename) : null;
                    $data = [
                        'patient_name' => $prescription->patient_name,
                        'prescriber_name' => $prescription->prescriber_name,
                        'prescription_number' => $prescription->prescription_number,
                        'issued_at' => optional($prescription->issued_at)->format('Y-m-d'),
                        'document_url' => $documentUrl,
                        'ocr_details' => $details,
                    ];
                    $encodedDetails = htmlspecialchars(
                        json_encode($data, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_HEX_TAG),
                        ENT_QUOTES,
                        'UTF-8'
                    );

                    return '<button type="button" class="btn btn-link prescription-patient-detail-link p-0" data-details="'.$encodedDetails.'" title="View prescription details">'
                        .e($prescription->patient_name ?: 'Pending review')
                        .'</button>';
                })
                ->editColumn('status', function ($prescription) {
                    $badge = $prescription->status === 'approved'
                        ? 'success'
                        : ($prescription->status === 'rejected' ? 'danger' : 'warning');

                    return '<span class="badge badge-'.$badge.'">'.ucfirst((string) $prescription->status).'</span>';
                })
                ->addColumn('action', function ($prescription) {
                    $menuItems = '';
                    if ($prescription->document_path) {
                        $filename = basename($prescription->document_path);
                        $documentUrl = url('storage/system/prescriptions/'.$filename);
                        $menuItems .= '<a class="dropdown-item" href="'.e($documentUrl).'" target="_blank" rel="noopener">'
                            .'<i class="fas fa-file-medical mr-2"></i>View Prescription</a>'
                            .'<div class="dropdown-divider"></div>';
                    }

                    $formStart = '<form method="POST" action="'.e(route('prescriptions.status', $prescription)).'" class="prescription-action-form">'
                        .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
                        .'<input type="hidden" name="_method" value="PATCH">'
                        .'<div class="px-3 pb-2"><input name="verification_notes" class="form-control form-control-sm" maxlength="2000" placeholder="Verification notes" aria-label="Verification notes"></div>';
                    $menuItems .= $formStart
                        .'<button type="submit" name="status" value="approved" class="dropdown-item"><i class="fas fa-check-circle mr-2"></i>Approved</button>'
                        .'<button type="submit" name="status" value="rejected" class="dropdown-item text-danger"><i class="fas fa-times-circle mr-2"></i>Reject</button>'
                        .'<button type="submit" name="status" value="pending" class="dropdown-item"><i class="fas fa-clock mr-2"></i>Set as Pending</button>'
                        .'</form>'
                        .'<div class="dropdown-divider"></div>'
                        .'<form method="POST" action="'.e(route('prescriptions.destroy', $prescription)).'" onsubmit="return confirm(\'Delete this prescription and its uploaded document? This cannot be undone.\');">'
                        .'<input type="hidden" name="_token" value="'.e(csrf_token()).'">'
                        .'<input type="hidden" name="_method" value="DELETE">'
                        .'<button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash mr-2"></i>Delete</button>'
                        .'</form>';

                    return '<div class="btn-group">'
                        .'<button type="button" class="btn btn-sm btn-secondary dropdown-toggle prescription-action-button" data-patient-name="'.e($prescription->patient_name ?: 'Pending review').'" aria-haspopup="dialog" aria-expanded="false" aria-label="Prescription actions for '.e($prescription->patient_name ?: 'Pending review').'"><i class="fa fa-ellipsis-v"></i></button>'
                        .'<div class="dropdown-menu dropdown-menu-right">'.$menuItems.'</div>'
                        .'</div>';
                })
                ->rawColumns(['patient_name', 'status', 'action'])
                ->make(true);
        }

        return view('admin.prescriptions.index', [
            'title' => 'prescription verification',
        ]);
    }

    public function analyze(Request $request)
    {
        $rules = [
            'document' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'engine' => 'nullable|in:tesseract,gemini',
        ];
        if ($request->input('engine') === 'gemini') {
            $rules['credential_id'] = 'required|integer';
        }
        $request->validate($rules);

        $file = $request->file('document');
        if ($request->input('engine', 'tesseract') === 'gemini') {
            $credential = GeminiApiCredential::where('user_id', $request->user()->id)
                ->findOrFail($request->input('credential_id'));
            try {
                $apiKey = Crypt::decryptString($credential->encrypted_api_key);
            } catch (\Illuminate\Contracts\Encryption\DecryptException $exception) {
                return response()->json([
                    'message' => 'This saved Gemini key could not be decrypted. Remove it and create a new key profile.',
                ], 500);
            }

            return $this->analyzePrescriptionWithGemini($file, $apiKey);
        }

        $tesseract = env('TESSERACT_PATH');
        if (!$tesseract) {
            $tesseract = PHP_OS_FAMILY === 'Windows'
                ? 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe'
                : 'tesseract';
        }
        if (!is_file($tesseract)) {
            $tesseract = (new ExecutableFinder())->find($tesseract);
        }
        if (!$tesseract) {
            return response()->json([
                'message' => 'OCR is not configured. Install Tesseract OCR and set TESSERACT_PATH to its executable, then try again.',
            ], 503);
        }

        $sourcePath = $file->getRealPath();
        $imageSize = @getimagesize($sourcePath);
        $preparedPath = $this->preparePrescriptionImage($sourcePath);
        $passes = [
            ['path' => $preparedPath ?: $sourcePath, 'psm' => '4'],
            ['path' => $preparedPath ?: $sourcePath, 'psm' => '6'],
            ['path' => $preparedPath ?: $sourcePath, 'psm' => '11'],
        ];
        if ($preparedPath) {
            $passes[] = ['path' => $sourcePath, 'psm' => '6'];
        }

        $bestResult = null;
        $timedOut = false;
        try {
            foreach ($passes as $pass) {
                try {
                    $process = new Process([
                        $tesseract,
                        $pass['path'],
                        'stdout',
                        '-l',
                        'eng',
                        '--psm',
                        $pass['psm'],
                        '-c',
                        'preserve_interword_spaces=1',
                        'tsv',
                    ]);
                    $process->setTimeout(25);
                    $process->run();
                } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $exception) {
                    $timedOut = true;
                    continue;
                } catch (ProcessRuntimeException $exception) {
                    continue;
                }

                if (!$process->isSuccessful()) continue;
                $candidate = $this->parseTesseractTsv($process->getOutput());
                if ($candidate['text'] === '') continue;

                $candidate['quality'] = $candidate['confidence'] + min(20, $candidate['word_count'] * 0.5);
                if (!$bestResult || $candidate['quality'] > $bestResult['quality']) {
                    $bestResult = $candidate;
                }
            }

            $bestRotation = 0;
            $magick = (new ExecutableFinder())->find(env('IMAGEMAGICK_PATH', 'magick'));

            if ($magick) {
                foreach ([90, 180, 270] as $rotation) {
                    $rotatedPath = $this->preparePrescriptionImage($sourcePath, $rotation);
                    if (!$rotatedPath) continue;

                    try {
                        try {
                            $process = new Process([
                                $tesseract,
                                $rotatedPath,
                                'stdout',
                                '-l',
                                'eng',
                                '--psm',
                                '6',
                                '-c',
                                'preserve_interword_spaces=1',
                                'tsv',
                            ]);
                            $process->setTimeout(15);
                            $process->run();
                        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $exception) {
                            $timedOut = true;
                            continue;
                        } catch (ProcessRuntimeException $exception) {
                            continue;
                        }

                        if (!$process->isSuccessful()) continue;
                        $candidate = $this->parseTesseractTsv($process->getOutput());
                        if ($candidate['text'] === '') continue;

                        $candidate['quality'] = $candidate['confidence'] + min(20, $candidate['word_count'] * 0.5);
                        if (!$bestResult || $candidate['quality'] > $bestResult['quality']) {
                            $bestResult = $candidate;
                            $bestRotation = $rotation;
                        }
                    } finally {
                        if (is_file($rotatedPath)) @unlink($rotatedPath);
                    }
                }
            }
        } finally {
            if ($preparedPath && is_file($preparedPath)) @unlink($preparedPath);
        }

        if (!$bestResult) {
            if ($timedOut) {
                return response()->json(['message' => 'OCR took too long. Try a smaller or clearer image.'], 504);
            }
            return response()->json(['message' => 'OCR could not read this image. Try a clearer JPG or PNG.'], 422);
        }

        $regionalText = $bestRotation === 0
            ? $this->ocrPrescriptionRegions($tesseract, $sourcePath)
            : [];
        $combinedText = implode("\n\n", array_filter(array_values($regionalText)));
        $pageDraft = $this->formatPrescriptionDraft($bestResult['text']);
        $regionalDraft = $combinedText !== '' ? $this->formatPrescriptionDraft($combinedText) : '';
        $recognizedText = $this->mergePrescriptionDrafts($pageDraft, $regionalDraft);
        if ($recognizedText === '') {
            return response()->json([
                'recognized_text' => '',
                'matches' => [],
                'message' => 'No readable text was found. Try a clearer image or enter the prescription details manually.',
            ]);
        }

        $matches = $this->findCatalogCandidates($bestResult['text']."\n".$combinedText);
        $wordAnalysis = $this->extractTesseractWordBoxes($tesseract, $sourcePath, $bestRotation);
        return response()->json([
            'recognized_text' => $recognizedText,
            'ocr_confidence' => $bestResult['confidence'],
            'ocr_words' => $wordAnalysis['words'],
            'image_size' => $wordAnalysis['image_size'] ?: ($imageSize ? ['width' => $imageSize[0], 'height' => $imageSize[1]] : null),
            'orientation_corrected' => $bestRotation !== 0,
            'orientation_rotation' => $bestRotation,
            'matches' => array_slice($matches, 0, 8),
            'message' => $matches
                ? 'Possible catalog matches found. Confirm every item against the original prescription.'
                : 'No catalog matches were found. Review the recognized text and verify the prescription manually.',
        ]);
    }

    public function geminiCredentials(Request $request)
    {
        $credentials = GeminiApiCredential::where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static function ($credential) {
                return ['id' => $credential->id, 'name' => $credential->name];
            });

        return response()->json(['credentials' => $credentials]);
    }

    public function storeGeminiCredential(Request $request)
    {
        $request->merge(['name' => trim((string) $request->input('name'))]);
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                \Illuminate\Validation\Rule::unique('gemini_api_credentials', 'name')
                    ->where('user_id', $request->user()->id),
            ],
            'api_key' => 'required|string|min:20|max:512',
        ]);

        $credential = GeminiApiCredential::create([
            'user_id' => $request->user()->id,
            'name' => $data['name'],
            'encrypted_api_key' => Crypt::encryptString(trim($data['api_key'])),
        ]);

        return response()->json([
            'credential' => ['id' => $credential->id, 'name' => $credential->name],
            'message' => 'Gemini key profile created.',
        ], 201);
    }

    public function deleteGeminiCredential(Request $request, int $credential)
    {
        $deleted = GeminiApiCredential::where('user_id', $request->user()->id)
            ->whereKey($credential)
            ->delete();
        if (!$deleted) {
            abort(404);
        }

        return response()->json(['message' => 'Gemini key profile deleted.']);
    }

    private function analyzePrescriptionWithGemini($file, string $apiKey)
    {
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return response()->json(['message' => 'Gemini analysis supports JPEG, PNG, and WebP images only.'], 422);
        }
        $imageContents = @file_get_contents($file->getRealPath());
        if ($imageContents === false) {
            return response()->json(['message' => 'The uploaded image could not be read. Please choose it again and retry.'], 500);
        }

        try {
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(60)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent', [
                    'contents' => [[
                        'parts' => [
                            [
                                'text' => 'Transcribe all legible text from this prescription image faithfully. Preserve the original wording, spelling, numbers, and line breaks. Do not guess unreadable text, infer missing details, interpret the prescription, or add advice. Return only the transcription.',
                            ],
                            [
                                'inline_data' => [
                                    'mime_type' => $mimeType,
                                    'data' => base64_encode($imageContents),
                                ],
                            ],
                        ],
                    ]],
                    'generationConfig' => ['temperature' => 0.1],
                ]);
        } catch (ConnectionException $exception) {
            return response()->json([
                'message' => 'Could not connect to Google Gemini. Check the internet connection and try again.',
            ], 503);
        }

        if (!$response->successful()) {
            $providerMessage = $response->json('error.message');
            $providerMessage = is_string($providerMessage) ? trim($providerMessage) : '';
            if ($providerMessage !== '') {
                $providerMessage = str_replace($apiKey, '[redacted]', $providerMessage);
                $providerMessage = mb_substr($providerMessage, 0, 500);
            }
            Log::warning('Google Gemini prescription analysis failed.', [
                'http_status' => $response->status(),
                'provider_status' => $response->json('error.status'),
                'provider_message' => $providerMessage,
            ]);

            if ($response->status() === 429) {
                return response()->json([
                    'message' => 'Google Gemini rate limit or quota reached. Check the project quota and try again.'
                        .($providerMessage !== '' ? ' Google: '.$providerMessage : ''),
                ], 429);
            }
            if (in_array($response->status(), [400, 401, 403, 404], true)) {
                return response()->json([
                    'message' => 'Google Gemini rejected the request (HTTP '.$response->status().'). Check the API key, key restrictions, Gemini API access, and selected model.'
                        .($providerMessage !== '' ? ' Google: '.$providerMessage : ''),
                ], 422);
            }

            return response()->json([
                'message' => 'Google Gemini returned HTTP '.$response->status().'. Try again or use Built-in OCR.'
                    .($providerMessage !== '' ? ' Google: '.$providerMessage : ''),
            ], 502);
        }

        $parts = $response->json('candidates.0.content.parts', []);
        $recognizedText = trim(implode("\n", array_filter(array_map(static function ($part) {
            return is_array($part) && is_string($part['text'] ?? null) ? $part['text'] : '';
        }, is_array($parts) ? $parts : []))));
        if ($recognizedText === '') {
            return response()->json([
                'message' => 'Google Gemini did not return readable text. Try a clearer image or use Built-in OCR.',
            ], 422);
        }

        $draft = $this->formatPrescriptionDraft($recognizedText);
        $matches = $this->findCatalogCandidates($recognizedText);

        return response()->json([
            'recognized_text' => $draft,
            'analysis_method' => 'gemini',
            'matches' => array_slice($matches, 0, 8),
            'message' => 'Google Gemini transcription is ready. Verify every extracted detail against the original prescription.',
        ]);
    }

    public function matchCatalog(Request $request)
    {
        $data = $request->validate([
            'recognized_text' => 'required|string|max:20000',
        ]);
        $matches = $this->findCatalogCandidates($data['recognized_text']);

        return response()->json([
            'matches' => $matches,
            'message' => $matches
                ? 'Possible catalog matches found. Confirm every item against the original prescription.'
                : 'No catalog matches were found. Review the recognized text and verify the prescription manually.',
        ]);
    }

    private function findCatalogCandidates(string $recognizedText): array
    {
        $normalize = static function ($value) {
            $value = strtolower((string) $value);
            $value = preg_replace('/[^a-z0-9]+/', ' ', $value);
            return trim(preg_replace('/\s+/', ' ', $value));
        };

        $normalizedText = ' '.$normalize($recognizedText).' ';
        $lines = array_values(array_filter(array_map($normalize, preg_split('/\R+/', $recognizedText))));
        $matches = [];

        $products = Product::with('purchase')
            ->where('is_active', true)
            ->whereHas('purchase')
            ->get(['id', 'purchase_id', 'price']);

        foreach ($products as $product) {
            $name = $normalize(optional($product->purchase)->product);
            if (strlen($name) < 3) continue;

            $score = strpos($normalizedText, ' '.$name.' ') !== false ? 100 : 0;
            if ($score < 100) {
                $nameWords = explode(' ', $name);
                foreach ($lines as $line) {
                    $words = explode(' ', $line);
                    $minimumWindow = max(1, count($nameWords) - 1);
                    $maximumWindow = min(count($words), count($nameWords) + 1);
                    for ($windowSize = $minimumWindow; $windowSize <= $maximumWindow; $windowSize++) {
                        for ($start = 0; $start + $windowSize <= count($words); $start++) {
                            similar_text($name, implode(' ', array_slice($words, $start, $windowSize)), $similarity);
                            $score = max($score, $similarity);
                        }
                    }
                }
            }

            if ($score >= 70) {
                $matches[] = [
                    'id' => $product->id,
                    'name' => optional($product->purchase)->product ?: 'Unnamed product',
                    'confidence' => (int) round($score),
                    'price' => (float) $product->price,
                    'stock' => (int) optional($product->purchase)->quantity,
                ];
            }
        }

        usort($matches, static function ($left, $right) {
            return $right['confidence'] <=> $left['confidence'];
        });

        return array_slice($matches, 0, 8);
    }

    private function preparePrescriptionImage(string $sourcePath, int $rotation = 0): ?string
    {
        $magick = (new ExecutableFinder())->find(env('IMAGEMAGICK_PATH', 'magick'));
        if (!$magick) return null;

        $temporaryBase = tempnam(sys_get_temp_dir(), 'prescription-ocr-');
        if (!$temporaryBase) return null;

        @unlink($temporaryBase);
        $preparedPath = $temporaryBase.'.png';
        $keepPreparedFile = false;

        try {
            $arguments = [
                $magick,
                $sourcePath,
                '-auto-orient',
            ];
            if ($rotation !== 0) {
                $arguments[] = '-rotate';
                $arguments[] = (string) $rotation;
            }
            $arguments = array_merge($arguments, [
                '-gravity',
                'center',
                '-resize',
                '2200x2200>',
                '-colorspace',
                'Gray',
                '-contrast-stretch',
                '1%x99%',
                '-level',
                '15%,85%,1.2',
                '-sharpen',
                '0x1.0',
                '-deskew',
                '40%',
                '-trim',
                '+repage',
                '-bordercolor',
                'white',
                '-border',
                '20x20',
                '-threshold',
                '60%',
                $preparedPath,
            ]);
            $process = new Process($arguments);
            $process->setTimeout(20);
            $process->run();

            if ($process->isSuccessful() && is_file($preparedPath)) {
                $keepPreparedFile = true;
                return $preparedPath;
            }

            return null;
        } catch (ProcessRuntimeException $exception) {
            return null;
        } catch (\Symfony\Component\Process\Exception\ProcessTimedOutException $exception) {
            return null;
        } finally {
            if (!$keepPreparedFile && is_file($preparedPath)) @unlink($preparedPath);
        }
    }

    private function ocrPrescriptionRegions(string $tesseract, string $sourcePath): array
    {
        $magick = (new ExecutableFinder())->find(env('IMAGEMAGICK_PATH', 'magick'));
        if (!$magick || !is_file($sourcePath)) {
            return [];
        }

        try {
            $dimensionsProcess = new Process([$magick, 'identify', '-format', '%w %h', $sourcePath]);
            $dimensionsProcess->setTimeout(10);
            $dimensionsProcess->run();
            if (!$dimensionsProcess->isSuccessful()) {
                return [];
            }

            $dimensions = preg_split('/\s+/', trim($dimensionsProcess->getOutput()));
            $imageWidth = (int) ($dimensions[0] ?? 0);
            $imageHeight = (int) ($dimensions[1] ?? 0);
            if ($imageWidth < 1 || $imageHeight < 1) {
                return [];
            }
        } catch (\Throwable $exception) {
            return [];
        }

        $regionRatios = [
            'header' => [0.00, 0.25],
            'patient' => [0.02, 0.30],
            'medication' => [0.24, 0.42],
            'doctor' => [0.68, 0.30],
        ];
        $regions = [];
        foreach ($regionRatios as $name => [$topRatio, $heightRatio]) {
            $top = min($imageHeight - 1, (int) round($imageHeight * $topRatio));
            $height = min($imageHeight - $top, max(1, (int) round($imageHeight * $heightRatio)));
            $regions[$name] = $imageWidth.'x'.$height.'+0+'.$top;
        }

        $results = [];
        foreach ($regions as $name => $crop) {
            $temporaryBase = tempnam(sys_get_temp_dir(), 'prescription-region-');
            if (!$temporaryBase) continue;
            @unlink($temporaryBase);
            $croppedPath = $temporaryBase.'.png';

            try {
                $cropProcess = new Process([
                    $magick,
                    $sourcePath,
                    '-crop',
                    $crop,
                    '+repage',
                    '-colorspace',
                    'Gray',
                    '-normalize',
                    '-contrast-stretch',
                    '1%x99%',
                    $croppedPath,
                ]);
                $cropProcess->setTimeout(15);
                $cropProcess->run();

                if (!$cropProcess->isSuccessful() || !is_file($croppedPath)) {
                    @unlink($croppedPath);
                    continue;
                }

                $ocrProcess = new Process([
                    $tesseract,
                    $croppedPath,
                    'stdout',
                    '-l',
                    'eng',
                    '--psm',
                    '6',
                    '-c',
                    'preserve_interword_spaces=1',
                    'tsv',
                ]);
                $ocrProcess->setTimeout(20);
                $ocrProcess->run();

                if ($ocrProcess->isSuccessful()) {
                    $parsed = $this->parseTesseractTsv($ocrProcess->getOutput());
                    if ($parsed['text'] !== '') {
                        $results[$name] = $parsed['text'];
                    }
                }
            } catch (\Throwable $exception) {
                // Ignore region OCR failures and continue with the best page-level result.
            } finally {
                if (is_file($croppedPath)) {
                    @unlink($croppedPath);
                }
            }
        }

        return $results;
    }

    private function parseTesseractTsv(string $output): array
    {
        $rows = preg_split('/\R/', trim($output));
        $header = str_getcsv(array_shift($rows) ?: '', "\t");
        $indexes = array_flip($header);
        $groupedWords = [];
        $confidences = [];
        $wordBoxes = [];

        foreach ($rows as $row) {
            $columns = str_getcsv($row, "\t");
            if (($columns[$indexes['level'] ?? -1] ?? '') !== '5') continue;

            $word = trim((string) ($columns[$indexes['text'] ?? -1] ?? ''));
            $confidence = (float) ($columns[$indexes['conf'] ?? -1] ?? -1);
            if ($word === '' || $confidence < 0) continue;

            $lineKey = implode(':', [
                $columns[$indexes['block_num'] ?? -1] ?? 0,
                $columns[$indexes['par_num'] ?? -1] ?? 0,
                $columns[$indexes['line_num'] ?? -1] ?? 0,
            ]);
            $groupedWords[$lineKey][] = $word;
            $confidences[] = $confidence;
            $wordBoxes[] = [
                'text' => $word,
                'confidence' => round($confidence),
                'left' => (int) ($columns[$indexes['left'] ?? -1] ?? 0),
                'top' => (int) ($columns[$indexes['top'] ?? -1] ?? 0),
                'width' => (int) ($columns[$indexes['width'] ?? -1] ?? 0),
                'height' => (int) ($columns[$indexes['height'] ?? -1] ?? 0),
            ];
        }

        $text = implode("\n", array_map(static function ($words) {
            return implode(' ', $words);
        }, array_values($groupedWords)));

        return [
            'text' => trim($text),
            'confidence' => $confidences ? round(array_sum($confidences) / count($confidences)) : 0,
            'word_count' => count($confidences),
            'words' => $wordBoxes,
        ];
    }

    private function extractTesseractWordBoxes(string $tesseract, string $sourcePath, int $rotation = 0): array
    {
        $ocrPath = $sourcePath;
        $rotatedPath = null;

        try {
            if ($rotation !== 0) {
                $magick = (new ExecutableFinder())->find(env('IMAGEMAGICK_PATH', 'magick'));
                $temporaryBase = $magick ? tempnam(sys_get_temp_dir(), 'prescription-words-') : false;
                if (!$magick || !$temporaryBase) {
                    return ['words' => [], 'image_size' => null];
                }

                @unlink($temporaryBase);
                $rotatedPath = $temporaryBase.'.jpg';
                $rotateProcess = new Process([
                    $magick,
                    $sourcePath,
                    '-auto-orient',
                    '-rotate',
                    (string) $rotation,
                    '-background',
                    'white',
                    '-alpha',
                    'remove',
                    $rotatedPath,
                ]);
                $rotateProcess->setTimeout(15);
                $rotateProcess->run();
                if (!$rotateProcess->isSuccessful() || !is_file($rotatedPath)) {
                    return ['words' => [], 'image_size' => null];
                }
                $ocrPath = $rotatedPath;
            }

            $process = new Process([
                $tesseract,
                $ocrPath,
                'stdout',
                '-l',
                'eng',
                '--psm',
                '11',
                'tsv',
            ]);
            $process->setTimeout(10);
            $process->run();

            if (!$process->isSuccessful()) {
                return ['words' => [], 'image_size' => null];
            }

            $imageSize = @getimagesize($ocrPath);
            return [
                'words' => array_values(array_filter($this->parseTesseractTsv($process->getOutput())['words'], static function ($word) {
                    return $word['width'] > 0 && $word['height'] > 0;
                })),
                'image_size' => $imageSize ? ['width' => $imageSize[0], 'height' => $imageSize[1]] : null,
            ];
        } catch (\Throwable $exception) {
            return ['words' => [], 'image_size' => null];
        } finally {
            if ($rotatedPath && is_file($rotatedPath)) {
                @unlink($rotatedPath);
            }
        }
    }

    private function formatPrescriptionDraft(string $text): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($text));
        if ($normalized === '') {
            return '';
        }

        $normalized = preg_replace('/(?<=[A-Za-z])(?=[A-Z][a-z])/', ' ', $normalized);
        $normalized = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $normalized);
        $normalized = preg_replace('/(?<=\d)(?=[A-Za-z])|(?<=[A-Za-z])(?=\d)/', ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        $replacements = [
            'PatientNo' => 'Patient No',
            'PatientNo.' => 'Patient No',
            'PatientType' => 'Patient Type',
            'ConsultDate' => 'Consult Date',
            'ConsultationNo' => 'Consultation No',
            'AppointmentDate' => 'Appointment Date',
            'BirthDate' => 'Birth Date',
            'DoctorName' => 'Doctor Name',
            'PatientName' => 'Patient Name',
            'LicenseNo' => 'License No',
            'Page' => 'Page',
        ];

        foreach ($replacements as $search => $replace) {
            $normalized = preg_replace('/\b'.preg_quote($search, '/').'\b/i', $replace, $normalized);
        }

        $patterns = [
            'Date' => '/(?<!Birth )(?<!Consult )(?<!Appointment )\bDate\b\s*[:.-]?\s*(.*?)(?=\b(?:Patient\s+No|Patient\s+Type|Age|Birth\s+Date|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Patient No' => '/\bPatient\s+No\.?\b\s*[:.-]?\s*(.*?)(?=\b(?:Patient\s+Type|Age|Birth\s+Date|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Patient Type' => '/\bPatient\s+Type\b\s*[:.-]?\s*(.*?)(?=\b(?:Age|Birth\s+Date|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Age' => '/\bAge\b\s*[:.-]?\s*(.*?)(?=\b(?:Birth\s+Date|Sex|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Birth Date' => '/\bBirth\s+Date\b\s*[:.-]?\s*(.*?)(?=\b(?:Patient\s+No|Patient\s+Type|Sex|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Consult Date' => '/\bConsult\s+Date\b\s*[:.-]?\s*(.*?)(?=\b(?:Consultation\s+No|Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Consultation No' => '/\bConsultation\s+No\.?\b\s*[:.-]?\s*(.*?)(?=\b(?:Appointment\s+Date|Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Appointment Date' => '/\bAppointment\s+Date\b\s*[:.-]?\s*(.*?)(?=\b(?:Patient\s+Name|Rx|Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Patient Name' => '/(?<!Doctor )(?<!Physician )(?<!Product )(?<!Brand )\b(?:Patient\s+)?Name\b\s*[:.-]?\s*(.*?)(?=\b(?:Date|DOB|Birth\s+Date|Address|Gender|Sex|Age|Patient\s+No|Patient\s+Type|Consult\s+Date|Consultation\s+No|Appointment\s+Date|Rx|Doctor\s+Name|License\s+No|DEA|NPI|Page)\b|$)/i',
            'Rx' => '/\bRx\b\s*[:.-]?\s*(.*?)(?=\b(?:Doctor\s+Name|License\s+No|Page)\b|$)/i',
            'Doctor Name' => '/\bDoctor\s+Name\b\s*[:.-]?\s*(.*?)(?=\b(?:License\s+No|Page)\b|$)/i',
            'License No' => '/\bLicense\s+No\.?\b\s*[:.-]?\s*(.*?)(?=\bPage\b|$)/i',
        ];

        $fields = [];
        foreach ($patterns as $label => $pattern) {
            $searchText = $label === 'Patient Name'
                ? (preg_split('/\bRx\b/i', $normalized, 2)[0] ?? $normalized)
                : $normalized;
            if (preg_match($pattern, $searchText, $matches)) {
                $value = trim((string) ($matches[1] ?? ''));
                $value = preg_replace('/\s+/', ' ', $value);
                $value = trim($value, " .:-");
                if ($value !== '') {
                    $fields[$label] = $value;
                }
            }
        }

        $output = [];
        foreach (['Date', 'Patient No', 'Age', 'Birth Date', 'Consult Date', 'Patient Type', 'Consultation No', 'Appointment Date', 'Patient Name', 'Rx', 'Doctor Name', 'License No'] as $label) {
            if (isset($fields[$label])) {
                $output[] = $label.': '.$fields[$label];
            }
        }

        return implode("\n", $output) ?: $normalized;
    }

    private function mergePrescriptionDrafts(string $pageDraft, string $regionalDraft): string
    {
        if ($regionalDraft === '') {
            return $pageDraft;
        }

        $labels = [
            'Date', 'Patient No', 'Patient Type', 'Age', 'Birth Date', 'Consult Date',
            'Consultation No', 'Appointment Date', 'Patient Name', 'Rx', 'Doctor Name', 'License No',
        ];
        $pageFields = $this->prescriptionDraftFields($pageDraft, $labels);
        $regionalFields = $this->prescriptionDraftFields($regionalDraft, $labels);
        $fields = [];

        foreach ($labels as $label) {
            $value = $pageFields[$label] ?? $regionalFields[$label] ?? null;
            if ($value !== null && $value !== '') {
                $fields[$label] = $value;
            }
        }

        if (!$fields) {
            return $pageDraft !== '' ? $pageDraft : $regionalDraft;
        }

        $output = [];
        foreach ($labels as $label) {
            if (isset($fields[$label])) {
                $output[] = $label.': '.$fields[$label];
            }
        }

        return implode("\n", $output);
    }

    private function prescriptionDraftFields(string $draft, array $labels): array
    {
        $fields = [];
        foreach (preg_split('/\R/', $draft) ?: [] as $line) {
            if (!preg_match('/^\s*([^:]+)\s*:\s*(.*?)\s*$/', $line, $matches)) {
                continue;
            }

            $lineLabel = mb_strtolower(preg_replace('/[^a-z]/i', '', $matches[1]));
            foreach ($labels as $label) {
                $labelKey = mb_strtolower(preg_replace('/[^a-z]/i', '', $label));
                if ($lineLabel === $labelKey && trim($matches[2]) !== '') {
                    $fields[$label] = trim($matches[2]);
                    break;
                }
            }
        }

        return $fields;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prescription_number' => 'nullable|string|max:100',
            'patient_name' => 'nullable|string|max:150',
            'prescriber_name' => 'nullable|string|max:150',
            'issued_at' => 'nullable|string|max:40',
            'ocr_details' => 'nullable|json|max:20000',
            'document' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $submittedOcrDetails = json_decode($data['ocr_details'] ?? '[]', true);
        unset($data['ocr_details']);
        $data['ocr_details'] = [];
        foreach (['patient_name', 'age', 'birth_date', 'consult_date', 'patient_type', 'patient_no', 'consultation_no', 'appointment_date', 'rx', 'doctor_name', 'license_no'] as $field) {
            $value = $submittedOcrDetails[$field] ?? null;
            if (is_scalar($value) && trim((string) $value) !== '') {
                $data['ocr_details'][$field] = mb_substr(trim((string) $value), 0, 2000);
            }
        }

        $data['patient_name'] = trim((string) ($data['patient_name'] ?? '')) ?: 'Pending review';
        $data['prescriber_name'] = trim((string) ($data['prescriber_name'] ?? '')) ?: null;
        $issuedAt = trim((string) ($data['issued_at'] ?? ''));
        $issuedAtTimestamp = $issuedAt === '' ? false : strtotime($issuedAt);
        $data['issued_at'] = $issuedAtTimestamp === false ? null : date('Y-m-d', $issuedAtTimestamp);

        if ($request->hasFile('document')) {
            $data['document_path'] = 'system/prescriptions/'.app(OrganizedFileStorage::class)->store($request->file('document'), 'prescriptions');
        }
        unset($data['document']);
        $data['submitted_by'] = $request->user()->id;
        $data['status'] = 'pending';
        Prescription::create($data);

        return back()->with(notify('Prescription submitted for verification'));
    }

    public function updateStatus(Request $request, Prescription $prescription)
    {
        $data = $request->validate([
            'status' => 'required|in:approved,rejected,pending',
            'verification_notes' => 'nullable|string|max:2000',
        ]);
        $data['verified_by'] = $request->user()->id;
        $data['verified_at'] = now();
        $prescription->update($data);

        return back()->with(notify('Prescription verification updated'));
    }

    public function destroy(Prescription $prescription)
    {
        $documentFilename = $prescription->document_path
            ? basename($prescription->document_path)
            : null;

        $prescription->delete();
        app(OrganizedFileStorage::class)->delete('prescriptions', $documentFilename);

        return back()->with(notify('Prescription deleted'));
    }
}