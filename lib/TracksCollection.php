<?php

namespace Hananils;

use Countable;
use Iterator;
use Kirby\Database\Database;
use Kirby\Database\Query;
use Hananils\Tracker;

class TracksCollection implements Iterator, Countable
{
    protected $database;
    protected $table;

    private $data = [];
    private $filters = [];
    private $sort = [];
    private $limit = 100;
    private $offset = 0;
    private $validate = false;

    private $key = 0;

    public function __construct($table = 'tracks')
    {
        $tracker = new Tracker();
        $this->database = $tracker->toDatabase();
        $this->table = $table;
    }

    public function filterBy($column, $comparison, $value)
    {
        $this->filters[] = [$column, $comparison, $value];

        return $this;
    }

    public function limit(int $limit = 20)
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset = 0)
    {
        $this->offset = $offset;

        return $this;
    }

    public function sortBy()
    {
        $this->sort = func_get_args();

        return $this;
    }

    public function validate()
    {
        $this->validate = true;

        return $this;
    }

    public function fetch()
    {
        if (!empty($this->data)) {
            return;
        }

        $query = new Query($this->database, $this->table);
        $query->iterator('array');

        foreach ($this->filters as $filter) {
            $query->where($filter[0], $filter[1], $filter[2]);
        }

        $query->limit($this->limit);
        $query->offset($this->offset);

        if (!empty($this->sort)) {
            $query->order(implode(' ', $this->sort));
        }

        // Model data
        foreach ($query->all() as $data) {
            $item = new Track($data, $this->database);

            if (!$this->validate || $item->isValid()) {
                $this->data[] = $item;
            }
        }
    }

    public function rewind()
    {
        $this->fetch();

        if ($this->data) {
            $keys = array_keys($this->data);
            $this->key = $keys[0];
        } else {
            $this->key = 0;
        }
    }

    public function current()
    {
        $this->fetch();
        return $this->data[$this->key];
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        $this->fetch();

        $keys = array_keys($this->data);
        $position = array_search($this->key, $keys);

        if (isset($keys[$position + 1])) {
            $this->key = $keys[$position + 1];
        } else {
            $this->key = null;
        }
    }

    public function valid()
    {
        $this->fetch();
        return isset($this->data[$this->key]);
    }

    public function count()
    {
        $this->fetch();
        return count($this->data);
    }

    private function normalize($data)
    {
        return array_map(function ($item) {
            if (is_array($item)) {
                return $this->normalize($item);
            } elseif (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }
        }, $data);
    }

    public function toArray()
    {
        $this->fetch();
        return $this->normalize($this->data);
    }

    public function __debugInfo()
    {
        $this->fetch();
        return $this->data;
    }
}
