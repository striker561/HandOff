<?php

use App\Enums\Project\ProjectCurrency;
use App\Enums\Project\ProjectStatus;
use App\Models\Project;
use App\Models\User;

it('exposes project status labels and badge colors', function () {
    expect(ProjectStatus::ACTIVE->label())->toBe('Active')
        ->and(ProjectStatus::ACTIVE->badgeColor())->toBe('blue')
        ->and(ProjectStatus::ON_HOLD->label())->toBe('On hold')
        ->and(ProjectStatus::ON_HOLD->badgeColor())->toBe('amber');
});

it('exposes project currency labels and symbols', function () {
    expect(ProjectCurrency::USD->label())->toBe('USD ($)')
        ->and(ProjectCurrency::USD->symbol())->toBe('$')
        ->and(ProjectCurrency::NGN->symbol())->toBe('₦')
        ->and(ProjectCurrency::EUR->symbol())->toBe('€');
});

it('formats project budget on the model', function () {
    $client = User::factory()->create();
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'budget' => 1500.50,
        'currency' => ProjectCurrency::USD,
    ]);

    expect($project->formatted_budget)->toBe('$1,500.50');
});

it('builds a list summary for compact project rows', function () {
    $client = User::factory()->create(['name' => 'Acme Corp']);
    $project = Project::factory()->create([
        'client_unique_id' => $client->unique_id,
        'budget' => 2500,
        'currency' => ProjectCurrency::EUR,
        'due_date' => '2026-08-15',
    ]);

    expect($project->list_summary)->toContain('Acme Corp')
        ->and($project->list_summary)->toContain('€2,500.00')
        ->and($project->list_summary)->toContain('Aug 15, 2026');
});
