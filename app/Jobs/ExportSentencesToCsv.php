<?php

namespace App\Jobs;

use App\Models\Sentence;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportSentencesToCsv implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $fileName;
    public $filePath;
    public $userId;

    public function __construct($userId)
    {
        $this->fileName = 'sentences_export_'.now()->format('Ymd_His').'.csv';
        $this->filePath = 'public/exports/'.$this->fileName;
        $this->userId = $userId;
    }

    public function handle()
    {
        try {
            // Создаем директорию, если не существует
            $exportDir = storage_path('app/public/exports');
            if (!file_exists($exportDir)) {
                mkdir($exportDir, 0755, true);
            }

            $filePath = $exportDir.'/'.$this->fileName;

            $handle = fopen($filePath, 'w');
            if (!$handle) {
                throw new \Exception("Failed to create file: {$filePath}");
            }

            // Заголовки CSV
            fputcsv($handle, ['ID', 'Предложение', 'Перевод', 'Author', 'Date']);

            // Экспорт данных
            Sentence::where('status', 2)
                ->with(['translations', 'translations.user'])
                ->orderBy('id')
                ->chunk(1000, function ($sentences) use ($handle) {
                    foreach ($sentences as $sentence) {
                        $translation = $sentence->translations->first();
                        fputcsv($handle, [
                            $sentence->id,
                            $sentence->sentence,
                            $translation->translation ?? '',
                            $translation->user->name ?? '',
                            $sentence->created_at
                        ]);
                    }
                });

            fclose($handle);

            // Сохраняем имя файла для этого задания
            Cache::put('export_file_'.$this->job->getJobId(), $this->fileName, now()->addHours(2));

        } catch (\Exception $e) {
            Log::error("Export failed: ".$e->getMessage());
            throw $e;
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error("Export job failed: ".$exception->getMessage());
    }
}