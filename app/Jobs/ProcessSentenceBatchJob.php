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

        $total = Cache::increment('processed_sentences', count($this->sentences));

        foreach ($this->sentences as $sentence) {
            $trimmedSentence = trim($sentence);
            $length = mb_strlen($trimmedSentence);

            if (!empty($trimmedSentence)) {
                $price = match (true) {
                    $length <= 100 => 6,
                    $length <= 200 => 12,
                    default => 18,
                };

                Sentence::create([
                    'sentence' => $trimmedSentence,
                    'price' => $price,
                ]);
            }
        }
    }
}

