<?php

namespace Hananils;

use Kirby\Database\Database;
use F;

class Tracker
{
    protected $database;

    public function __construct()
    {
        $file = kirby()->root('site') . '/logs/tracker.sqlite';

        if (!F::exists($file)) {
            F::write($file, '');
        }

        $this->database = new Database([
            'type' => 'sqlite',
            'database' => $file
        ]);

        $this->createTracks();
        $this->createNotifications();
    }

    public function track(
        $model = 'page',
        $action = 'update',
        $new = null,
        $old = null,
        $field = true,
        $toString = null
    ) {
        if (
            !$action ||
            in_array($action, option('hananils.tracker.excluded')) ||
            option('hananils.tracker.' . $model) !== true
        ) {
            return;
        }

        $user = kirby()
            ->user()
            ->id();

        if (option('hananils.tracker.randomUser') === true) {
            $user = kirby()
                ->users()
                ->shuffle()
                ->first()
                ->id();
        }

        if ($new) {
            $kid = $new->id();
            $changes = $this->diff($new, $old, $field, $toString);
        } else {
            $kid = $old->id();
            $changes = null;
        }

        $data = [
            'user' => $user,
            'kid' => $kid,
            'model' => $model,
            'action' => $action,
            'changes' => $changes
        ];

        $this->database->table('tracks')->insert($data);

        $references = $this->references($new, $old);
        if (!empty($references)) {
            $track = $this->database->lastId();

            foreach ($references as $reference) {
                $data = [
                    'kid' => $reference['kid'],
                    'track' => $track,
                    'status' => $reference['status']
                ];

                $this->database->table('notifications')->insert($data);
            }
        }
    }

    private function references($new = null, $old = null)
    {
        if (!$new || !method_exists($new, 'blueprint')) {
            return;
        }

        $references = [];

        foreach ($new->blueprint()->fields() as $field) {
            if (in_array($field['type'], ['pages', 'files', 'users'])) {
                $name = $field['name'];

                $before = $old
                    ? $old
                        ->content()
                        ->get($name)
                        ->yaml()
                    : [];
                $after = $new
                    ? $new
                        ->content()
                        ->get($name)
                        ->yaml()
                    : [];

                $all = array_values($before) + array_values($after);
                $added = array_diff($after, $before);
                $removed = array_diff($before, $after);

                foreach ($all as $kid) {
                    $status = 0;

                    if ($old === null) {
                        $status = 1;
                    } elseif (in_array($kid, $added)) {
                        $status = 1;
                    } elseif (in_array($kid, $removed)) {
                        $status = -1;
                    }

                    $references[] = [
                        'kid' => $kid,
                        'status' => $status
                    ];
                }
            }
        }

        return $references;
    }

    private function diff($new, $old, $field = true, $toString = false)
    {
        $fields = [];

        if ($field === true) {
            $new = $new->content->data();
            $old = $old->content->data();

            foreach ($new as $field => $value) {
                if (
                    isset($old[$field]) &&
                    trim($old[$field]) !== trim($value)
                ) {
                    $fields[] = $field;
                }
            }
        } elseif ($field !== false) {
            if ($toString) {
                if (!empty($old)) {
                    $fields[] = $old->{$field}()->{$toString}();
                }
                $fields[] = $new->{$field}()->{$toString}();
            } else {
                if (!empty($old)) {
                    $fields[] = $old->{$field}();
                }
                $fields[] = $new->{$field}();
            }
        }

        return implode(',', $fields);
    }

    private function createTracks()
    {
        if (!$this->database->validateTable('tracks')) {
            $this->database->execute('CREATE TABLE "tracks" (
                "id" integer,
                "user" varchar,
                "datetime" datetime DEFAULT CURRENT_TIMESTAMP,
                "kid" varchar,
                "model" varchar,
                "action" varchar,
                "changes" text,
                PRIMARY KEY (id)
            );');
        }
    }

    private function createNotifications()
    {
        if (!$this->database->validateTable('notifications')) {
            $this->database->execute('CREATE TABLE "notifications" (
                "id" integer,
                "kid" varchar,
                "datetime"  datetime DEFAULT CURRENT_TIMESTAMP,
                "track" integer,
                "status" integer DEFAULT "0",
                PRIMARY KEY("id"),
                FOREIGN KEY("track") REFERENCES "tracks"("id")
            );');
        }
    }

    public function toDatabase()
    {
        return $this->database;
    }
}
