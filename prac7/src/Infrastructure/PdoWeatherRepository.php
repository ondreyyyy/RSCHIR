<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Weather;
use App\Domain\WeatherRepository;
use PDO;

final class PdoWeatherRepository implements WeatherRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    public function all(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, city, temperature, description, humidity, pressure, recorded_at FROM weather ORDER BY id'
        );

        return array_map(
            static fn(array $row): Weather => Weather::fromArray($row),
            $stmt->fetchAll(PDO::FETCH_ASSOC)
        );
    }

    public function find(int $id): ?Weather
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, city, temperature, description, humidity, pressure, recorded_at FROM weather WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Weather::fromArray($row) : null;
    }

    public function create(Weather $weather): Weather
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO weather (city, temperature, description, humidity, pressure, recorded_at) '
            . 'VALUES (:city, :temperature, :description, :humidity, :pressure, :recorded_at)'
        );
        $stmt->execute([
            ':city' => $weather->city,
            ':temperature' => $weather->temperature,
            ':description' => $weather->description,
            ':humidity' => $weather->humidity,
            ':pressure' => $weather->pressure,
            ':recorded_at' => $weather->recordedAt,
        ]);

        return new Weather(
            (int) $this->pdo->lastInsertId(),
            $weather->city,
            $weather->temperature,
            $weather->description,
            $weather->humidity,
            $weather->pressure,
            $weather->recordedAt
        );
    }

    public function update(Weather $weather): Weather
    {
        $stmt = $this->pdo->prepare(
            'UPDATE weather SET city = :city, temperature = :temperature, description = :description, '
            . 'humidity = :humidity, pressure = :pressure, recorded_at = :recorded_at WHERE id = :id'
        );
        $stmt->execute([
            ':city' => $weather->city,
            ':temperature' => $weather->temperature,
            ':description' => $weather->description,
            ':humidity' => $weather->humidity,
            ':pressure' => $weather->pressure,
            ':recorded_at' => $weather->recordedAt,
            ':id' => $weather->id,
        ]);

        return $weather;
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM weather WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM weather')->fetchColumn();
    }
}
