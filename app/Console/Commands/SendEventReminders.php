<?php

namespace App\Console\Commands;

use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Str;


class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-event-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envoie des notifications à tous les participants de l\'événement quand l\'évenement commence bientôt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = \App\Models\Event::with('attendees.user')
            ->whereBetween('start_time', [now(), now()->addDay()])
            ->get();

        $eventCount = $events->count();
        $eventLabel = Str::plural('évenement', $eventCount);

        $this->info("Trouvé {$eventCount} {$eventLabel}.");

        $events->each(
            fn($event) => $event->attendees->each(
                // fn($attendee) => $this->info("Notifier le user {$attendee->user->id}")
                fn($attendee) => $attendee->user->notify(
                    new EventReminderNotification(
                        $event
                    )
                )
            )
        );

        $this->info('Notification de rappel envoyée avec succes !');
    }
}
