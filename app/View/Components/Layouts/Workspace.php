<?php

namespace App\View\Components\Layouts;

use App\Enums\WorkspaceType;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Workspace extends Component
{
    public WorkspaceType $workspace;

    public function __construct(public ?string $title = null)
    {
        /** @var User $user */
        $user = auth()->user();

        $this->workspace = WorkspaceType::forUser($user);
    }

    public function render(): View
    {
        return view('layouts.app.shell');
    }
}
