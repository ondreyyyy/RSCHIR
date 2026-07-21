<?php

namespace App\Domain;

interface WeatherRepository
{
    /** @return Weather[] */
    public function all(): array;

    public function find(int $id): ?Weather;

    public function create(Weather $weather): Weather;

    public function update(Weather $weather): Weather;

    public function delete(int $id): void;

    public function count(): int;
}
