<?php

namespace Database\Seeders;

use App\Models\LanguagePhrase;
use Illuminate\Database\Seeder;

class LanguagePhraseSeeder extends Seeder
{
    public function run(): void
    {
        $json = database_path('seeders/language_phrases.json');
        $data = json_decode($json, true);

        if (! is_array($data) || ! isset($data['language_phrases'])) {
            $this->command->error('Invalid JSON structure: missing "language_phrases"');

            return;
        }

        $rows = collect($data['language_phrases'])
            ->map(function ($item) {
                return [
                    'language_id' => (int) ($item['language_id'] ?? 0),
                    'phrase' => (string) ($item['phrase'] ?? ''),
                    'translated' => (string) ($item['translated'] ?? ''),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })
            // basic sanity filter
            ->filter(fn ($r) => $r['language_id'] && $r['phrase'] !== '')
            ->values()
            ->all();

        // Use upsert so rerunning updates translations without duplicating rows
        LanguagePhrase::upsert(
            $rows,
            ['language_id', 'phrase'],        // unique-by
            ['translated', 'updated_at']      // columns to update
        );

        $this->command->info('Language phrases imported: '.count($rows));
    }
}
