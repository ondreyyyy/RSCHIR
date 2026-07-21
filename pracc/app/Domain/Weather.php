<?php

namespace App\Domain;

final class Weather
{
    public function __construct(
        public ?int $id,
        public string $city,
        public float $temperature,
        public string $description,
        public int $humidity,
        public int $pressure,
        public string $recordedAt
    ) {
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'city' => $this->city,
            'temperature' => $this->temperature,
            'description' => $this->description,
            'humidity' => $this->humidity,
            'pressure' => $this->pressure,
            'recorded_at' => $this->recordedAt,
        ];
    }

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id']) ? (int) $row['id'] : null,
            (string) ($row['city'] ?? ''),
            (float) ($row['temperature'] ?? 0),
            (string) ($row['description'] ?? ''),
            (int) ($row['humidity'] ?? 0),
            (int) ($row['pressure'] ?? 0),
            (string) ($row['recorded_at'] ?? '')
        );
    }
}
