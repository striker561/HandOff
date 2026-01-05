<?php

namespace Database\Seeders;

use App\Models\{
    User,
    Project,
    Milestone,
    Deliverable,
    DeliverableFile,
    Meeting,
    Credential,
    Comment,
    ActivityLog,
    Notification
};
use App\Enums\User\AccountRole;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

// THIS IS AI ASSISTED TO SAVE TIME 
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->command->info('🌱 Starting database seeding...');

        // ---------- Users ----------
        $this->command->info('Creating users...');
        $users = User::factory(10)->create();
        $this->command->info("Created {$users->count()} users");

        // ---------- Projects, Milestones, Deliverables, Meetings, Credentials ----------
        $this->command->info('Creating projects, milestones, deliverables, meetings, credentials...');
        $allProjects = collect();
        $allMilestones = collect();
        $allDeliverables = collect();

        foreach ($users as $user) {
            $projects = Project::factory(rand(2, 5))->for(
                $user,
                AccountRole::CLIENT->value
            )->create();

            $allProjects = $allProjects->merge($projects);

            foreach ($projects as $project) {
                $milestones = collect();
                $milestoneCount = rand(2, 5);

                for ($i = 0; $i < $milestoneCount; $i++) {
                    $milestone = Milestone::factory()->for($project, 'project')->create(['order' => $i]);
                    $milestones->push($milestone);
                    $allMilestones->push($milestone);

                    $deliverables = collect();
                    $deliverableCount = rand(1, 3);
                    for ($j = 0; $j < $deliverableCount; $j++) {
                        $deliverable = Deliverable::factory()
                            ->for($project, 'project')
                            ->for($milestone, 'milestone')
                            ->for($users->random(), 'createdBy')
                            ->create(['order' => $j]);

                        $deliverables->push($deliverable);
                        $allDeliverables->push($deliverable);

                        DeliverableFile::factory(rand(1, 4))
                            ->for($deliverable, 'deliverable')
                            ->for($users->random(), 'uploadedBy')
                            ->create();
                    }
                }

                Meeting::factory(rand(2, 6))
                    ->for($project, 'project')
                    ->for($users->random(), 'scheduledBy')
                    ->create();

                if ($allDeliverables->isNotEmpty()) {
                    Meeting::factory(rand(1, 3))
                        ->for($project, 'project')
                        ->for($allDeliverables->random(), 'deliverable')
                        ->for($users->random(), 'scheduledBy')
                        ->create();
                }

                Credential::factory(rand(1, 5))->for($project, 'project')->create();
            }
        }

        $this->command->info("Created {$allProjects->count()} projects");
        $this->command->info("Created {$allMilestones->count()} milestones");
        $this->command->info("Created {$allDeliverables->count()} deliverables");

        // ---------- Helper for polymorphic factories ----------
        $createPolymorphic = function ($items, $factoryMethod, $min, $max) use ($users) {
            $total = 0;
            foreach ($items as $item) {
                $count = rand($min, $max);
                $factoryMethod($count)->for($item, 'commentable')->create(['user_unique_id' => $users->random()->unique_id]);
                $total += $count;
            }
            return $total;
        };

        // ---------- Comments ----------
        $this->command->info('Creating comments...');
        $totalComments = 0;
        $totalComments += $createPolymorphic($allProjects, fn($count) => Comment::factory($count)->forProject(), 5, 20);
        $totalComments += $createPolymorphic($allMilestones, fn($count) => Comment::factory($count)->forMilestone(), 5, 15);
        $totalComments += $createPolymorphic($allDeliverables, fn($count) => Comment::factory($count)->forDeliverable(), 5, 20);
        $this->command->info("Created {$totalComments} comments");

        // ---------- ActivityLogs ----------
        $this->command->info('Creating activity logs...');
        $createActivity = function ($items, $factoryMethod, $min, $max) use ($users) {
            $total = 0;
            foreach ($items as $item) {
                $count = rand($min, $max);
                ActivityLog::factory(rand(1, 5))->created()->for($item, 'subject')->causedBy($users->random())->create(['user_unique_id' => $users->random()->unique_id]);
                ActivityLog::factory(rand(2, 10))->updated()->for($item, 'subject')->causedBy($users->random())->create(['user_unique_id' => $users->random()->unique_id]);
                ActivityLog::factory(rand(1, 5))->for($item, 'subject')->causedBy($users->random())->create(['user_unique_id' => $users->random()->unique_id]);
                $total += $count;
            }
            return $total;
        };

        $totalActivityLogs = 0;
        $totalActivityLogs += $createActivity($allProjects, fn($count) => ActivityLog::factory($count)->forProject(), 5, 30);
        $totalActivityLogs += $createActivity($allMilestones, fn($count) => ActivityLog::factory($count)->forMilestone(), 5, 20);
        $totalActivityLogs += $createActivity($allDeliverables, fn($count) => ActivityLog::factory($count)->forDeliverable(), 5, 25);
        $this->command->info("Created {$totalActivityLogs} activity logs");

        // ---------- Notifications ----------
        $this->command->info('Creating notifications...');
        $totalNotifications = 0;
        foreach ($users as $user) {
            foreach ([$allProjects, $allDeliverables, $allMilestones] as $collection) {
                if ($collection->isEmpty())
                    continue;
                $count = rand(1, 10);
                $factory = Notification::factory($count);
                $factory->for($collection->random(), $collection === $allProjects ? 'project' : ($collection === $allDeliverables ? 'deliverable' : 'milestone'))
                    ->create(['user_unique_id' => $user->unique_id]);
                $totalNotifications += $count;
            }
        }
        $this->command->info("Created {$totalNotifications} notifications");

        // ---------- Summary ----------
        $this->command->newLine();
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->table(
            ['Entity', 'Count'],
            [
                ['Users', $users->count()],
                ['Projects', $allProjects->count()],
                ['Milestones', $allMilestones->count()],
                ['Deliverables', $allDeliverables->count()],
                ['Comments', $totalComments],
                ['Activity Logs', $totalActivityLogs],
                ['Notifications', $totalNotifications],
            ]
        );
    }
}
