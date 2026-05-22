<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Browser\DuskTestCase;
use Tests\TestCase as BaseTestCase;

uses(BaseTestCase::class)->in('Feature');
uses(DuskTestCase::class, DatabaseMigrations::class)->in('Browser');
