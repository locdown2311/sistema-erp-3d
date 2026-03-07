<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\PixelDrainService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UploadImageToPixelDrain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;
    public $localPath;
    public $fieldName;
    public $arrayIndex;
    
    /**
     * Create a new job instance.
     *
     * @param Model $model Example: Product instance
     * @param string $localPath Existing file path in storage disk
     * @param string $fieldName Field we update with the url (e.g. 'image_path' or 'store_logo')
     * @param int|null $arrayIndex If set, update this index within a JSON array field
     */
    public function __construct(Model $model, string $localPath, string $fieldName = 'image_path', ?int $arrayIndex = null)
    {
        $this->model = $model;
        $this->localPath = $localPath;
        $this->fieldName = $fieldName;
        $this->arrayIndex = $arrayIndex;
    }

    /**
     * Execute the job.
     */
    public function handle(PixelDrainService $pixelDrain): void
    {
        try {
            if (!Storage::disk('public')->exists($this->localPath)) {
                Log::error("UploadImageToPixelDrain: File not found {$this->localPath}");
                return;
            }

            $fullPath = Storage::disk('public')->path($this->localPath);
            $mimeType = \Illuminate\Support\Facades\File::mimeType($fullPath);
            $extension = \Illuminate\Support\Facades\File::extension($fullPath);
            
            // Generate a clean user-facing name instead of the internal temporary id
            $cleanName = 'imagem.' . ($extension ?: 'jpg');
            
            $file = new UploadedFile($fullPath, $cleanName, $mimeType, null, true);

            $url = $pixelDrain->uploadFile($file);

            if ($url) {
                if ($this->arrayIndex !== null) {
                    // Use a transaction with pessimistic locking to prevent race conditions
                    // when multiple jobs update the same array field concurrently.
                    DB::transaction(function () use ($url) {
                        // Re-fetch the model with a lock
                        $lockedModel = get_class($this->model)::where('id', $this->model->id)->lockForUpdate()->first();
                        
                        if ($lockedModel) {
                            $currentArray = $lockedModel->getAttribute($this->fieldName);
                            if (is_string($currentArray)) {
                                $currentArray = json_decode($currentArray, true) ?? [];
                            } elseif (!is_array($currentArray)) {
                                $currentArray = [];
                            }
                            
                            $currentArray[$this->arrayIndex] = $url;
                            
                            // Re-index array to ensure it's a JSON array not object
                            $currentArray = array_values($currentArray);
                            
                            $lockedModel->{$this->fieldName} = $currentArray;
                            $lockedModel->save();
                        }
                    });
                } else {
                    // Update model field with PixelDrain URL (simple string)
                    $this->model->update([
                        $this->fieldName => $url
                    ]);
                }
                
                // Clean up local file
                Storage::disk('public')->delete($this->localPath);
            }
        } catch (\Exception $e) {
            Log::error("UploadImageToPixelDrain failed: " . $e->getMessage());
        }
    }
}
