<?php

namespace App\Enums\Deliverable;

enum DeliverableType: string
{
    case FILE = 'file';
    case LINK = 'link';
    case TEXT = 'text';
    case CODE = 'code';
    case SCOPE = 'scope';
    case OTHER = 'other';
    case DESIGN = 'design';
    case RESEARCH = 'research';

    public function label(): string
    {
        return match ($this) {
            self::FILE => __('File'),
            self::LINK => __('Link'),
            self::TEXT => __('Text'),
            self::CODE => __('Code'),
            self::SCOPE => __('Scope'),
            self::OTHER => __('Other'),
            self::DESIGN => __('Design'),
            self::RESEARCH => __('Research'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::FILE => 'document',
            self::LINK => 'link',
            self::TEXT => 'document-text',
            self::CODE => 'code-bracket',
            self::SCOPE => 'clipboard-document-list',
            self::OTHER => 'cube',
            self::DESIGN => 'paint-brush',
            self::RESEARCH => 'magnifying-glass',
        };
    }

    public function isFileBased(): bool
    {
        return in_array($this, [
            self::FILE,
            self::DESIGN,
            self::CODE,
            self::OTHER,
        ], true);
    }

    public function isLink(): bool
    {
        return $this === self::LINK;
    }

    public function isTextBased(): bool
    {
        return in_array($this, [
            self::TEXT,
            self::SCOPE,
            self::RESEARCH,
        ], true);
    }
}
