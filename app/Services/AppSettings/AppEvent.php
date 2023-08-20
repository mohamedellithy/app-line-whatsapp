<?php
namespace App\Services\AppSettings;

interface AppEvent {

    public function __construct($data);

    public function set_log();

    public function resolve_event();
}
