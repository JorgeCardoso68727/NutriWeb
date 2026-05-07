<?php

namespace app\commands;

use app\models\Event;
use app\models\EventParticipant;
use Yii;
use yii\console\Controller;
use yii\db\Query;

class EventCompleteController extends Controller
{
    /**
     * Marks events as completed if their end_date has passed and notifies participants.
     * This command should be run via cron job periodically.
     * 
     * Example: php yii event-complete
     * 
     * @return int
     */
    public function actionIndex()
    {
        $now = date('Y-m-d H:i:s');

        // Find all active events where end_date is in the past
        $completedEvents = Event::find()
            ->where(['status' => 'active'])
            ->andWhere(['<=', 'end_date', $now])
            ->all();

        if (empty($completedEvents)) {
            $this->stdout("No events to complete at this time.\n");
            return self::EXIT_CODE_NORMAL;
        }

        $completedCount = 0;
        $failedCount = 0;

        foreach ($completedEvents as $event) {
            try {
                $event->status = 'completed';
                if ($event->save(false)) {
                    $this->notifyParticipantsCompletion($event);
                    $completedCount++;
                    $this->stdout("✓ Event #{$event->id} ({$event->title}) marked as completed.\n");
                } else {
                    $failedCount++;
                    $this->stdout("✗ Failed to mark event #{$event->id} as completed.\n", self::FG_RED);
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->stdout("✗ Error processing event #{$event->id}: {$e->getMessage()}\n", self::FG_RED);
            }
        }

        $this->stdout("\n");
        $this->stdout("Summary: {$completedCount} events completed, {$failedCount} failed.\n");

        return self::EXIT_CODE_NORMAL;
    }

    /**
     * Notify all participants that the event has been completed.
     *
     * @param Event $event Event model
     */
    private function notifyParticipantsCompletion(Event $event): void
    {
        try {
            $participants = EventParticipant::find()
                ->where(['event_id' => $event->id, 'status' => 'registered'])
                ->with('user')
                ->all();

            $eventUrl = Yii::$app->urlManager->createAbsoluteUrl(['/event/view', 'id' => $event->id]);

            foreach ($participants as $participant) {
                if ($participant->user && $participant->user->email) {
                    try {
                        Yii::$app->mailer->compose('//email/event-completed', [
                            'user' => $participant->user,
                            'event' => $event,
                            'eventUrl' => $eventUrl,
                        ])
                            ->setTo($participant->user->email)
                            ->setFrom(Yii::$app->params['senderEmail'])
                            ->setSubject('Evento concluído: ' . htmlspecialchars($event->title, ENT_QUOTES, 'UTF-8'))
                            ->send();
                    } catch (\Exception $e) {
                        Yii::warning('Failed to send event completion email to ' . $participant->user->email . ': ' . $e->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            Yii::warning('Failed to notify participants of event completion: ' . $e->getMessage());
        }
    }
}
