<?php

namespace Hananils;

use DateTime;
use Kirby\Cms\App;
use Kirby\Database\Query;

class Track
{
    private $content;
    private $database;

    public function __construct($data, $database)
    {
        $this->content = $data;
        $this->database = $database;
    }

    public function __call(string $method, array $arguments = [])
    {
        if (isset($this->$method) === true) {
            return $this->$method;
        }

        return $this->content->$method;
    }

    public function hasColumn($column)
    {
        return isset($this->content->$column) ? true : false;
    }

    public function isValid()
    {
        return ($this->toReference() !== null);
    }

    public function toUser()
    {
        return App::instance()->user($this->content->user);
    }

    public function toReference()
    {
        if (!$this->hasColumn('kid')) {
            return null;
        }

        $id = $this->content->kid;

        if ($object = App::instance()->page($id)) {
            return $object;
        }

        if ($object = App::instance()->user($id)) {
            return $object;
        }

        if ($object = App::instance()->file($id)) {
            return $object;
        }

        return null;
    }

    public function toDate($format = null)
    {
        $date = new DateTime($this->content->datetime);

        if ($format) {
            return $date->format($format);
        }

        return $date;
    }

    public function toTrack()
    {
        if (!$this->hasColumn('track')) {
            return null;
        }

        $query = new Query($this->database, 'tracks');
        $query->iterator('array');
        $query->where('id', '=', $this->content->track);
        $query->limit(1);

        $result = $query->all();

        return new Track($result[0], $this->database);
    }

    public function toStatus($previous = false)
    {
        if (!$this->hasColumn('status')) {
            return null;
        }

        $status = intval($this->status());

        if ($status === 1) {
            return 'added';
        } else if ($status === -1) {
            return 'removed';
        }

        return 'unchanged';
    }

    public function value()
    {
        return implode(',', array_values($this->content));
    }

    public function toArray()
    {
        return $this->content->toArray();
    }

    public function __toString()
    {
        return $this->value();
    }

    public function __debugInfo()
    {
        return $this->toArray();
    }
};
