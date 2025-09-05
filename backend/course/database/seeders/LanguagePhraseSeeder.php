<?php

namespace Database\Seeders;

use App\Models\LanguagePhrase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class LanguagePhraseSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/json/language_phrases.json');

        if (! File::exists($path)) {
            $this->command->error("File not found: {$path}");

            return;
        }

        $json = File::get($path);                 // ← read file contents
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('JSON decode error: '.json_last_error_msg());

            return;
        }

        if (! isset($data['language_phrases']) || ! is_array($data['language_phrases'])) {
            $this->command->error('Invalid JSON structure: missing "language_phrases"');

            return;
        }

        $rows = collect($data['language_phrases'])
            ->map(fn ($item) => [
                'language_id' => (int) ($item['language_id'] ?? 0),
                'phrase' => (string) ($item['phrase'] ?? ''),
                'translated' => (string) ($item['translated'] ?? ''),
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->filter(fn ($r) => $r['language_id'] && $r['phrase'] !== '')
            ->values()
            ->all();

        // chunk if large
        collect($rows)->chunk(1000)->each(function ($chunk) {
            LanguagePhrase::upsert(
                $chunk->all(),
                ['language_id', 'phrase'],   // unique-by
                ['translated', 'updated_at'] // update columns
            );
        });

        $this->command->info('Language phrases imported: '.count($rows));
    }
}
