<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const SENSITIVE_COLUMNS = ['username', 'password', 'url', 'notes'];

    public function up(): void
    {
        DB::table('credentials')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row): void {
                $updates = [];

                foreach (self::SENSITIVE_COLUMNS as $column) {
                    $value = $row->{$column};

                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    if ($this->isEncrypted($value)) {
                        continue;
                    }

                    $updates[$column] = Crypt::encryptString($value);
                }

                if ($updates !== []) {
                    DB::table('credentials')->where('id', $row->id)->update($updates);
                }
            });
    }

    public function down(): void
    {
        DB::table('credentials')
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $row): void {
                $updates = [];

                foreach (self::SENSITIVE_COLUMNS as $column) {
                    $value = $row->{$column};

                    if (! is_string($value) || $value === '') {
                        continue;
                    }

                    if (! $this->isEncrypted($value)) {
                        continue;
                    }

                    try {
                        $updates[$column] = Crypt::decryptString($value);
                    } catch (Throwable) {
                        continue;
                    }
                }

                if ($updates !== []) {
                    DB::table('credentials')->where('id', $row->id)->update($updates);
                }
            });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
};
