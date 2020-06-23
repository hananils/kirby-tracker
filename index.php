<?php

load([
    'Hananils\\Tracker' => 'lib/Tracker.php',
    'Hananils\\TracksCollection' => 'lib/TracksCollection.php',
    'Hananils\\Track' => 'lib/Track.php'
], __DIR__);

Kirby::plugin('hananils/tracker', [
    'options' => [
        'site' => true,
        'page' => true,
        'user' => true,
        'file' => true,
        'excluded' => ['changeNum']
    ],
    'siteMethods' => [
        'tracks' => function ($limit = 20) {
            $tracks = new Hananils\TracksCollection();
            return $tracks->limit($limit);
        },
        'notifications' => function ($limit = 20) {
            $notifications = new Hananils\TracksCollection('notifications');
            return $notifications->limit($limit);
        }],
    'pageMethods' => [
        'tracks' => function ($limit = 20) {
            $tracks = new Hananils\TracksCollection();
            return $tracks->filterBy('kid', '=', $this->id())->limit($limit);
        },
        'notifications' => function ($limit = 20) {
            $notifications = new Hananils\TracksCollection('notifications');
            return $notifications->filterBy('kid', '=', $this->id())->limit($limit);
        }
    ],
    'userMethods' => [
        'tracks' => function ($limit = 20) {
            $tracks = new Hananils\TracksCollection();
            return $tracks->filterBy('user', '=', $this->id())->limit($limit);
        },
        'notifications' => function ($limit = 20) {
            $notifications = new Hananils\TracksCollection('notifications');
            return $notifications->filterBy('kid', '=', $this->id())->limit($limit);
        }
    ],
    'hooks' => [
        'file.changeName:after' => function ($newFile, $oldFile) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'changeName', $newFile, $oldFile, 'filename');
        },
        'file.changeSort:after' => function ($newFile, $oldFile) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'changeNum', $newFile, $oldFile, 'sort');
        },
        'file.create:after' => function ($file) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'create', null, $file, false);
        },
        'file.delete:after' => function ($status, $file) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'delete', $file, null, false);
        },
        'file.replace:after' => function ($newFile, $oldFile) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'delete', $newFile, $oldFile, false);
        },
        'file.update:after' => function ($newFile, $oldFile) {
            $tracker = new Hananils\Tracker();
            $tracker->track('file', 'update', $newFile, $oldFile);
        },
        'page.changeNum:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'changeNum', $newPage, $oldPage, 'num');
        },
        'page.changeSlug:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'changeSlug', $newPage, $oldPage, 'slug');
        },
        'page.changeStatus:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'changeStatus', $newPage, $oldPage, 'status');
        },
        'page.changeTemplate:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'changeTemplate', $newPage, $oldPage, 'template', 'name');
        },
        'page.changeTitle:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'changeTitle', $newPage, $oldPage, 'title', 'value');
        },
        'page.create:after' => function ($page) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'create', $page, null, false);
        },
        'page.delete:after' => function ($status, $page) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'delete', null, $page, false);
        },
        'page.duplicate:after' => function ($duplicatePage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'duplicate', null, $duplicatePage, false);
        },
        'page.update:after' => function ($newPage, $oldPage) {
            $tracker = new Hananils\Tracker();
            $tracker->track('page', 'update', $newPage, $oldPage);
        },
        'site.update:after' => function ($newSite, $oldSite) {
            $tracker = new Hananils\Tracker();
            $tracker->track('site', 'update', $newSite, $oldSite);
        },
        'user.changeEmail:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'changeEmail', $newUser, $oldUser, 'email');
        },
        'user.changeLanguage:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'changeLanguage', $new, $old, 'language');
        },
        'user.changeName:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'changeName', $newUser, $oldUser, 'name');
        },
        'user.changePassword:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'changePassword', $newUser, null, false);
        },
        'user.changeRole:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'changeRole', $newUser, null, 'role');
        },
        'user.create:after' => function ($user) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'update', null, $user, false);
        },
        'user.delete:after' => function ($status, $user) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'update', $user, null, false);
        },
        'user.login:after' => function ($user, $session) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'login', $user, null, false);
        },
        'user.logout:before' => function ($user, $session) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'logout', $user, null, false);
        },
        'user.update:after' => function ($newUser, $oldUser) {
            $tracker = new Hananils\Tracker();
            $tracker->track('user', 'update', $newUser, $oldUser);
        }
    ]
]);
