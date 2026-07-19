<?php
declare(strict_types=1);

namespace App\Application\Weather;

use App\Domain\Weather;
use App\Domain\WeatherRepository;

final class WeatherService
{
    public function __construct(
        private WeatherRepository $repository
    ) {
    }

    /** @return Weather[] */
    public function all(): array
    {
        return $this->repository->all();
    }

    public function find(int $id): ?Weather
    {
        return $this->repository->find($id);
    }

    public function create(array $data): Weather
    {
        $validated = $this->validate($data);
        return $this->repository->create(Weather::fromArray($validated));
    }

    public function update(int $id, array $data): Weather
    {
        $validated = $this->validate($data);
        $validated['id'] = $id;
        return $this->repository->update(Weather::fromArray($validated));
    }

    public function delete(int $id): void
    {
        $this->repository->delete($id);
    }

    /** @return Weather[] последняя запись для каждого города */
    public function latestPerCity(): array
    {
        $rows = $this->repository->all();
        $latest = [];
        foreach ($rows as $row) {
            $city = $row->city;
            if (!isset($latest[$city]) || $row->recordedAt > ($latest[$city]->recordedAt ?? '')) {
                $latest[$city] = $row;
            }
        }

        return array_values($latest);
    }

    public function ensureFixtures(int $minCount = 50): void
    {
        if ($this->repository->count() < $minCount) {
            $this->generateFixtures($minCount - $this->repository->count());
        }
    }

    public function generateFixtures(int $count = 50): void
    {
        if (!class_exists(\Faker\Generator::class)) {
            return;
        }

        $faker = \Faker\Factory::create('ru_RU');
        $cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Казань'];
        $descriptions = ['Ясно', 'Облачно', 'Дождь', 'Снег', 'Туман'];

        for ($i = 0; $i < $count; $i++) {
            $this->repository->create(new Weather(
                null,
                $faker->randomElement($cities),
                (float) $faker->numberBetween(-10, 30),
                $faker->randomElement($descriptions),
                (int) $faker->numberBetween(30, 95),
                (int) $faker->numberBetween(980, 1040),
                $faker->dateTimeBetween('-90 days', 'now')->format('Y-m-d')
            ));
        }
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty($data['city'])) {
            $errors[] = 'Поле city обязательно.';
        }
        if (!isset($data['temperature']) || !is_numeric($data['temperature'])) {
            $errors[] = 'Поле temperature должно быть числом.';
        }
        if (empty($data['description'])) {
            $errors[] = 'Поле description обязательно.';
        }
        if (!isset($data['humidity']) || !is_numeric($data['humidity'])) {
            $errors[] = 'Поле humidity должно быть числом.';
        }
        if (!isset($data['pressure']) || !is_numeric($data['pressure'])) {
            $errors[] = 'Поле pressure должно быть числом.';
        }
        if (empty($data['recorded_at'])) {
            $errors[] = 'Поле recorded_at обязательно.';
        }
        if ($errors) {
            throw new \InvalidArgumentException(implode(' ', $errors));
        }

        return [
            'city' => trim((string) $data['city']),
            'temperature' => (float) $data['temperature'],
            'description' => trim((string) $data['description']),
            'humidity' => (int) $data['humidity'],
            'pressure' => (int) $data['pressure'],
            'recorded_at' => trim((string) $data['recorded_at']),
        ];
    }
}
