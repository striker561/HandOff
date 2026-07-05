<?php

namespace App\Livewire\Ui;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class FileUploader extends Component
{
    use WithFileUploads;

    /**
     * @var array{
     *     existing: list<array{id: string, label: string, size?: int}>,
     *     pending: list<mixed>,
     *     removed_ids: list<string>
     * }
     */
    public array $state = [
        'existing' => [],
        'pending' => [],
        'removed_ids' => [],
    ];

    public bool $canUpload = true;

    public int $maxUploadKilobytes;

    public int $maxFiles;

    public ?string $heading = null;

    public ?string $description = null;

    public ?string $uploadLabel = null;

    public ?string $lockedMessage = null;

    public ?string $emptyHeading = null;

    public ?string $emptyText = null;

    /**
     * @param  array<string, mixed>  $state
     */
    public function mount(
        array $state = [],
        ?int $maxUploadKilobytes = null,
        ?int $maxFiles = null,
    ): void {
        $this->maxUploadKilobytes = $maxUploadKilobytes ?? self::defaultMaxUploadKilobytes();
        $this->maxFiles = $maxFiles ?? self::defaultMaxFiles();
        $this->state = $this->normalizeState($state);
    }

    public function updatedStatePending(): void
    {
        if ($this->state['pending'] === []) {
            return;
        }

        $this->validate(
            self::rulesForState($this->state, maxFiles: $this->maxFiles, maxKilobytes: $this->maxUploadKilobytes),
            self::messagesForState(maxFiles: $this->maxFiles, maxKilobytes: $this->maxUploadKilobytes),
        );

        $this->dispatch('file-uploader-updated', state: $this->state);
    }

    public function removeExisting(int $index): void
    {
        if (! isset($this->state['existing'][$index])) {
            return;
        }

        $this->state['removed_ids'][] = $this->state['existing'][$index]['id'];
        unset($this->state['existing'][$index]);
        $this->state['existing'] = array_values($this->state['existing']);
        $this->state['removed_ids'] = array_values(array_unique($this->state['removed_ids']));

        $this->dispatch('file-uploader-updated', state: $this->state);
    }

    public function removePending(int $index): void
    {
        unset($this->state['pending'][$index]);
        $this->state['pending'] = array_values($this->state['pending']);
        $this->resetValidation('state.pending', 'state.pending.*');

        $this->dispatch('file-uploader-updated', state: $this->state);
    }

    #[Computed]
    public function uploadLimitLabel(): string
    {
        return self::formatKilobytesLabel($this->maxUploadKilobytes);
    }

    #[Computed]
    public function uploadErrorMessage(): string
    {
        $message = $this->getErrorBag()->first('state.pending')
            ?: $this->getErrorBag()->first('state.pending.*');

        if ($message === '') {
            return '';
        }

        if (str_contains(strtolower($message), 'failed to upload')) {
            return __('Upload failed. Ensure each file is under :limit and try again.', [
                'limit' => $this->uploadLimitLabel,
            ]);
        }

        return $message;
    }

    #[Computed]
    public function hasExistingItems(): bool
    {
        return $this->state['existing'] !== [];
    }

    #[Computed]
    public function hasPendingItems(): bool
    {
        return array_filter($this->state['pending']) !== [];
    }

    public static function defaultMaxUploadKilobytes(): int
    {
        return (int) config('handoff.uploads.max_kilobytes', 102400);
    }

    public static function defaultMaxFiles(): int
    {
        return (int) config('handoff.uploads.max_files', 20);
    }

    public static function formatKilobytesLabel(int $kilobytes): string
    {
        if ($kilobytes >= 1024) {
            return (int) round($kilobytes / 1024).' MB';
        }

        return $kilobytes.' KB';
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rulesForState(
        array $state,
        string $prefix = 'state',
        ?int $maxFiles = null,
        ?int $maxKilobytes = null,
    ): array {
        $maxFiles ??= self::defaultMaxFiles();
        $maxKilobytes ??= self::defaultMaxUploadKilobytes();
        $remaining = max(0, $maxFiles - count($state['existing'] ?? []));

        return [
            "{$prefix}.pending" => ['array', 'max:'.$remaining],
            "{$prefix}.pending.*" => ['file', 'max:'.$maxKilobytes],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messagesForState(
        string $prefix = 'state',
        ?int $maxFiles = null,
        ?int $maxKilobytes = null,
    ): array {
        $maxFiles ??= self::defaultMaxFiles();
        $maxKilobytes ??= self::defaultMaxUploadKilobytes();

        return [
            "{$prefix}.pending.max" => __('You can attach up to :count files.', ['count' => $maxFiles]),
            "{$prefix}.pending.*.file" => __('Only valid files are allowed.'),
            "{$prefix}.pending.*.max" => __('Each file must be :limit or smaller.', [
                'limit' => self::formatKilobytesLabel($maxKilobytes),
            ]),
        ];
    }

    public function render()
    {
        return view('livewire.ui.file-uploader');
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array{
     *     existing: list<array{id: string, label: string, size?: int}>,
     *     pending: list<mixed>,
     *     removed_ids: list<string>
     * }
     */
    private function normalizeState(array $state): array
    {
        return [
            'existing' => array_values($state['existing'] ?? []),
            'pending' => array_values($state['pending'] ?? []),
            'removed_ids' => array_values($state['removed_ids'] ?? []),
        ];
    }
}
