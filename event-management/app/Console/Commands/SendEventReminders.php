<?php

namespace App\Console\Commands;

use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Str;

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
    protected $description = 'Sends notifications to all event atttendes that event starts soon';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $events = \App\Models\Event::with('attendees.user') //set variable to event class with attendees (only want events with attendees)
        ->whereBetween('start_time', [now(), now()->addDay()]) //events that have start time between now and 24 hours
        ->get();// get all the events that fit into these parameters

    $eventCount = $events->count(); //count the total number of events within params
    $eventLabel = Str::plural('event', $eventCount); // change the label to plural if there is more than one event

    $this->info("Found {$eventCount} {$eventLabel}."); //output on the command line "found # event(s)"

    $events->each( //built in method to run closure functions (iterate) on every event in this collection
        fn($event) => $event->attendees->each( //each event has attendees which are also a collection to you can run each() again
            fn($attendee) => $attendee->user->notify( //this will notify each attendee in the event 
                new EventReminderNotification(
                    $event
                )
            )
        )
    );

        $this->info('Reminder notifications sent successfully!');
    }
}