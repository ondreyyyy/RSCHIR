<?php

namespace App\Infrastructure;

use App\Domain\Weather;
use App\Domain\WeatherRepository;
use App\Models\Weather as WeatherModel;

final class EloquentWeatherRepository implements WeatherRepository
{
    public function all(): array
    {
        return array_map(
            static fn(WeatherModel $model): Weather => Weather::fromArray(array_merge($model->toArray(), ['id' => $model->id])),
            WeatherModel::orderBy('id')->get()->all()
        );
    }

    public function find(int $id): ?Weather
    {
        $model = WeatherModel::find($id);
        if ($model === null) {
            return null;
        }

        return Weather::fromArray(array_merge($model->toArray(), ['id' => $model->id]));
    }

    public function create(Weather $weather): Weather
    {
        $model = new WeatherModel();
        $model->city = $weather->city;
        $model->temperature = $weather->temperature;
        $model->description = $weather->description;
        $model->humidity = $weather->humidity;
        $model->pressure = $weather->pressure;
        $model->recorded_at = $weather->recordedAt;
        $model->save();

        return new Weather(
            (int) $model->id,
            $model->city,
            (float) $model->temperature,
            $model->description,
            (int) $model->humidity,
            (int) $model->pressure,
            $model->recorded_at
        );
    }

    public function update(Weather $weather): Weather
    {
        $model = WeatherModel::find($weather->id);
        if ($model === null) {
            throw new \RuntimeException('Weather record not found.', 404);
        }

        $model->city = $weather->city;
        $model->temperature = $weather->temperature;
        $model->description = $weather->description;
        $model->humidity = $weather->humidity;
        $model->pressure = $weather->pressure;
        $model->recorded_at = $weather->recordedAt;
        $model->save();

        return $weather;
    }

    public function delete(int $id): void
    {
        WeatherModel::destroy($id);
    }

    public function count(): int
    {
        return WeatherModel::count();
    }
}
