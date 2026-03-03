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

class UploadImageToPixelDrain implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $model;
    public $localPath;
    public $fieldName;
    
    /**
     * Create a new job instance.
     *
     * @param Model $model Example: Product instance
     * @param string $localPath Existing file path in storage disk
     * @param string $fieldName Field we update with the url (e.g. 'image_path' or 'store_logo')
     */
    public function __construct(Model $model, string $localPath, string $fieldName = 'image_path')
    {
        $this->model = $model;
        $this->localPath = $localPath;
        $this->fieldName = $fieldName;
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
            
            // Generate a clean user-facing name instead of the internal tempoary id
            $cleanName = 'imagem.' . ($extension ?: 'jpg');
            
            $file = new UploadedFile($fullPath, $cleanName, $mimeType, null, true);

            $url = $pixelDrain->uploadFile($file);

            if ($url) {
                // Update model field with PixelDrain URL
                $this->model->update([
                    $this->fieldName => $url
                ]);
                
                // Clean up local file
                Storage::disk('public')->delete($this->localPath);
            }
        } catch (\Exception $e) {
            Log::error("UploadImageToPixelDrain failed: " . $e->getMessage());
        }
    }
}
