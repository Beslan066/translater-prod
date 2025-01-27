<?php

namespace App\Jobs;

use App\Models\Sentence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class ProcessSentenceBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    protected $sentences;

    public function __construct(array $sentences)
    {
        $this->sentences = $sentences;
    }

    public function handle()
    {
        try {
            // Данные для массовой вставки
            $data = [];

            foreach ($this->sentences as $sentence) {
                $trimmedSentence = trim($sentence);
                $length = mb_strlen($trimmedSentence);

                if (!empty($trimmedSentence)) {
                    // Определяем цену в зависимости от длины предложения
                    $price = match (true) {
                        $length <= 100 => 6,
                        $length <= 200 => 12,
                        default => 18,
                    };

                    // Формируем массив для массовой вставки
                    $data[] = [
                        'sentence' => $trimmedSentence,
                        'price' => $price,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            // Если есть данные для вставки, делаем массовую вставку
            if (!empty($data)) {
                Sentence::insert($data);
            }

            // Обновляем счетчик в кеше
            Cache::increment('processed_sentences', count($data));
        } catch (\Exception $e) {
            // Логируем ошибку
            Log::channel('sentence_jobs')->error('Ошибка в ProcessSentenceBatchJob: ' . $e->getMessage(), [
                'sentences' => $this->sentences,
                'trace' => $e->getTraceAsString(),
            ]);

            // Бросаем исключение, чтобы задача могла быть повторно обработана
            throw $e;
        }
    }

}

