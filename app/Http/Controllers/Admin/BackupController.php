<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ArchivedSupplier;
use App\Models\ArchiveEntry;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

use Log;
use Artisan;
use Storage;
use Response;
use Exception;
use League\Flysystem\Adapter\Local;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'backups';
        if (!count(config('backup.backup.destination.disks'))) {
            dd(trans('backup.no_disks_configured'));
        }
        $this->data['backups'] = [];
        foreach (config('backup.backup.destination.disks') as $disk_name) {
            $disk = Storage::disk($disk_name);
            $adapter = $disk->getDriver()->getAdapter();
            $files = $disk->allFiles();

            // make an array of backup files, with their filesize and creation date
            foreach ($files as $k => $f) {
                // only take the zip files into account
                if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                    $this->data['backups'][] = [
                        'file_path'     => $f,
                        'file_name'     => str_replace('backups/', '', $f),
                        'file_size'     => $disk->size($f),
                        'last_modified' => $disk->lastModified($f),
                        'disk'          => $disk_name,
                        'download'      => ($adapter instanceof Local) ? true : false,
                    ];
                }
            }
        }
        return view('admin.backup',$this->data,compact('title'));

    }

    public function archiveIndex()
    {
        $legacyArchives = ArchivedSupplier::latest('archived_at')->get()->map(function ($archive) {
            $archive->is_generic = false;
            return $archive;
        });
        $genericArchives = ArchiveEntry::latest('deleted_at')->get()->map(function ($archive) {
            return (object) [
                'id' => $archive->id,
                'supplier_name' => $archive->label,
                'archived_at' => $archive->deleted_at,
                'data' => ['type' => 'generic'],
                'model_type' => $archive->model_type,
                'is_generic' => true,
            ];
        });

        return view('admin.archive', [
            'title' => 'archive',
            'archivedSuppliers' => $legacyArchives->concat($genericArchives)->sortByDesc('archived_at')->values(),
        ]);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $notification = 'backup created successfully';
        $workDir = storage_path('app/backup-temp/'.uniqid('bundle_', true));
        $backupDir = storage_path('app/backups');
        $zipPath = $backupDir.'/pharmacy-bundle-'.date('Y-m-d-His').'.zip';
        try {
            ini_set('max_execution_time', 600);

            File::ensureDirectoryExists($workDir);
            File::ensureDirectoryExists($backupDir);
            $tables = [];
            foreach (DB::select('SHOW TABLES') as $table) {
                $tableName = array_values((array) $table)[0];
                $tables[$tableName] = DB::table($tableName)->get()->map(function ($row) {
                    return (array) $row;
                })->all();
            }
            File::put($workDir.'/database.json', json_encode($tables, JSON_PRETTY_PRINT));
            File::put($workDir.'/manifest.json', json_encode([
                'format' => 'pharmacy-bundle',
                'version' => 1,
                'created_at' => now()->toIso8601String(),
                'database_driver' => config('database.default'),
            ], JSON_PRETTY_PRINT));

            $zip = new ZipArchive();
            abort_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500, 'Unable to create backup ZIP.');
            $zip->addFile($workDir.'/manifest.json', 'manifest.json');
            $zip->addFile($workDir.'/database.json', 'database.json');
            foreach ([
                storage_path('app/system') => 'storage/system',
                storage_path('app/purchases') => 'storage/purchases',
                storage_path('app/public') => 'storage/public',
            ] as $source => $prefix) {
                if (!File::isDirectory($source)) continue;
                foreach (File::allFiles($source) as $file) {
                    $relativePath = str_replace('\\', '/', $file->getRelativePathname());
                    $zip->addFile($file->getPathname(), $prefix.'/'.$relativePath);
                }
            }
            $zip->close();
        } catch (Exception $e) {
            Log::error($e);
            if (File::isDirectory($workDir)) File::deleteDirectory($workDir);
            return back()->with(notify('Backup failed: '.$e->getMessage(), 'danger'));
        }

        if (File::isDirectory($workDir)) File::deleteDirectory($workDir);

        return back()->with($notification);
    }

    public function importBundle(Request $request)
    {
        $request->validate(['backup_file' => 'required|file|mimes:zip|max:512000']);
        $zip = new ZipArchive();
        abort_unless($zip->open($request->file('backup_file')->getRealPath()) === true, 422, 'Invalid backup ZIP.');
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        abort_unless(($manifest['format'] ?? null) === 'pharmacy-bundle', 422, 'Unsupported backup format.');
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = str_replace('\\', '/', $zip->getNameIndex($i));
            abort_if(strpos($name, '..') !== false || strpos($name, '/') === 0 || preg_match('/^[A-Za-z]:[\\\\\/]/', $name), 422, 'Unsafe backup path.');
        }

        $temp = storage_path('app/backup-temp/import_'.uniqid());
        File::ensureDirectoryExists($temp);
        $zip->extractTo($temp);
        $zip->close();

        try {
            $database = json_decode(File::get($temp.'/database.json'), true, 512, JSON_THROW_ON_ERROR);
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            foreach ($database as $table => $rows) {
                if (!Schema::hasTable($table)) continue;
                DB::table($table)->truncate();
                foreach (array_chunk($rows, 250) as $chunk) {
                    if ($chunk) DB::table($table)->insert($chunk);
                }
            }
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            foreach ([
                'storage/system' => storage_path('app/system'),
                'storage/purchases' => storage_path('app/purchases'),
                'storage/public' => storage_path('app/public'),
            ] as $source => $target) {
                if (File::isDirectory($temp.'/'.$source)) {
                    if (File::isDirectory($target)) File::deleteDirectory($target);
                    File::ensureDirectoryExists($target);
                    File::copyDirectory($temp.'/'.$source, $target);
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            File::deleteDirectory($temp);
        }

        return back()->with(notify('Backup imported successfully'));
    }

    
    

    /**
     * Downloads a backup zip file.
     */
    public function download(Request $request)
    {
        $disk = Storage::disk($request->input('disk'));
        $file_name = $request->input('file_path') ?: $request->input('file_name');
        $adapter = $disk->getDriver()->getAdapter();

        if ($adapter instanceof Local) {
            $storage_path = $disk->getDriver()->getAdapter()->getPathPrefix();

            if ($disk->exists($file_name)) {
                return response()->download($storage_path.$file_name);
            } else {
                abort(404, trans('backup.backup_doesnt_exist'));
            }
        } else {
            abort(404, trans('backup.only_local_downloads_supported'));
        }
    }



    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $disk = Storage::disk($request->input('disk'));
        $file_name = $request->input('file_path');

        if ($disk->exists($file_name)) {
            ArchiveEntry::create([
                'model_type' => 'backup',
                'model_id' => null,
                'label' => 'Backup: '.$file_name,
                'deleted_at' => now(),
                'data' => ['disk' => $request->input('disk'), 'file_path' => $file_name],
            ]);
            $disk->delete($file_name);
            $notification = notify('backup deleted successfully');
            return back()->with($notification);
        } else {
            abort(404, trans('backup.backup_doesnt_exist'));
        }
    }

    public function recoverArchive(ArchivedSupplier $archive)
    {
        if (($archive->data['type'] ?? null) === 'product') {
            $productData = $archive->data['product'] ?? [];
            $product = Product::withTrashed()->find($productData['id'] ?? null);

            if ($product) {
                $product->restore();
            } else {
                unset($productData['id'], $productData['created_at'], $productData['updated_at'], $productData['deleted_at']);
                Product::create($productData);
            }

            $archive->delete();

            return redirect()->route('products.index')->with(notify('Product recovered and returned to Products'));
        }

        DB::transaction(function () use ($archive) {
            $data = $archive->data;
            $supplierData = $data['supplier'];
            unset($supplierData['id'], $supplierData['created_at'], $supplierData['updated_at']);
            $supplier = Supplier::create($supplierData);

            foreach ($data['purchases'] as $entry) {
                $purchaseData = $entry['purchase'];
                unset($purchaseData['id'], $purchaseData['created_at'], $purchaseData['updated_at']);
                $purchaseData['supplier_id'] = $supplier->id;
                $purchase = Purchase::create($purchaseData);

                foreach ($entry['products'] as $productData) {
                    unset($productData['id'], $productData['created_at'], $productData['updated_at'], $productData['deleted_at']);
                    $productData['purchase_id'] = $purchase->id;
                    Product::create($productData);
                }
            }

            $archive->delete();
        });

        return back()->with(notify('Supplier and related records recovered'));
    }

    public function destroyArchive(ArchivedSupplier $archive)
    {
        $archive->delete();

        return back()->with(notify('Archived records deleted forever'));
    }

    public function restoreGenericArchive(ArchiveEntry $archive)
    {
        $modelClass = $archive->model_type;
        $restorable = [
            \App\Models\Product::class,
            \App\Models\Sale::class,
        ];

        abort_unless(in_array($modelClass, $restorable, true), 422, 'This archived record cannot be restored automatically.');
        $model = $modelClass::withTrashed()->find($archive->model_id);
        if ($model) {
            $model->restore();
        }

        $archive->delete();
        return back()->with(notify('Archived record restored'));
    }

    public function destroyGenericArchive(ArchiveEntry $archive)
    {
        $archive->delete();
        return back()->with(notify('Archived record deleted forever'));
    }
}
