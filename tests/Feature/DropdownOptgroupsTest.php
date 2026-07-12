<?php

declare(strict_types=1);

use Simtabi\Laranail\Enumerator\Presets\Enums\OrderStatusEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\PriorityEnum;
use Simtabi\Laranail\Enumerator\Presets\Enums\StatusEnum;

it('groups options by attribute:color', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="priority" groups-by="attribute:color" />',
        ['enum' => PriorityEnum::class],
    );

    // PriorityEnum has Low=secondary, Medium=info, High=warning,
    // Urgent=danger, Critical=danger — so two optgroups: 'danger' and others.
    $html->assertSee('<optgroup', false);
    $html->assertSee('label="danger"', false);
    $html->assertSee('label="warning"', false);
});

it('groups options using the enum HasGrouping::groups() map', function (): void {
    // OrderStatusEnum::groups() returns positive/negative/pending/terminal.
    $html = $this->blade(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" groups-by="groups" />',
        ['enum' => OrderStatusEnum::class],
    );

    $html->assertSee('<optgroup', false);
    $html->assertSee('label="positive"', false);
    $html->assertSee('label="pending"', false);
    $html->assertSee('label="terminal"', false);
});

it('groups options via an explicit map', function (): void {
    $html = $this->blade(
        '
        <x-laranail-enumerator::dropdown
            :enum="\\Simtabi\\Laranail\\Enumerator\\Presets\\Enums\\StatusEnum::class"
            name="status"
            :groups="$groups"
        />',
        ['groups' => [
            'Live' => [StatusEnum::Active],
            'Hidden' => [StatusEnum::Inactive, StatusEnum::Archived],
            'Waiting' => [StatusEnum::Pending],
        ]],
    );

    $html->assertSee('<optgroup label="Live"', false);
    $html->assertSee('<optgroup label="Hidden"', false);
    $html->assertSee('<optgroup label="Waiting"', false);
});

it('applies custom group-label map', function (): void {
    $html = $this->blade(
        '
        <x-laranail-enumerator::dropdown
            :enum="\\Simtabi\\Laranail\\Enumerator\\Presets\\Enums\\PublicationStatusEnum::class"
            name="post_status"
            groups-by="groups"
            :group-labels="[\'positive\' => \'Live posts\', \'negative\' => \'Removed\']"
        />',
    );

    $html->assertSee('<optgroup label="Live posts"', false);
    $html->assertSee('<optgroup label="Removed"', false);
});

it('keeps a flat option list when groups-by is null', function (): void {
    $html = $this->blade(
        '<x-laranail-enumerator::dropdown :enum="$enum" name="status" />',
        ['enum' => StatusEnum::class],
    );

    $html->assertDontSee('<optgroup', false);
    $html->assertSee('value="active"', false);
    $html->assertSee('value="pending"', false);
});
