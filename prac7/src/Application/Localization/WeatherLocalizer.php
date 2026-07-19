<?php
declare(strict_types=1);

namespace App\Application\Localization;

use App\Domain\Weather;

final class WeatherLocalizer
{
    private const MAPS = [
        'ru' => [
            'cities' => [
                'Москва' => 'Москва',
                'Санкт-Петербург' => 'Санкт-Петербург',
                'Новосибирск' => 'Новосибирск',
                'Казань' => 'Казань',
            ],
            'descriptions' => [
                'Ясно' => 'Ясно',
                'Облачно' => 'Облачно',
                'Дождь' => 'Дождь',
                'Туман' => 'Туман',
                'Снег' => 'Снег',
            ],
        ],
        'en' => [
            'cities' => [
                'Москва' => 'Moscow',
                'Санкт-Петербург' => 'Saint Petersburg',
                'Новосибирск' => 'Novosibirsk',
                'Казань' => 'Kazan',
            ],
            'descriptions' => [
                'Ясно' => 'Clear',
                'Облачно' => 'Cloudy',
                'Дождь' => 'Rain',
                'Туман' => 'Fog',
                'Снег' => 'Snow',
            ],
        ],
    ];

    /** @param Weather[] $rows */
    public function localize(string $language, array $rows): array
    {
        $map = self::MAPS[$language] ?? self::MAPS['ru'];
        $localized = [];

        foreach ($rows as $row) {
            $localized[] = [
                'id' => $row->id,
                'city' => $map['cities'][$row->city] ?? $row->city,
                'temperature' => $row->temperature,
                'description' => $map['descriptions'][$row->description] ?? $row->description,
                'humidity' => $row->humidity,
                'pressure' => $row->pressure,
                'recorded_at' => $row->recordedAt,
            ];
        }

        return $localized;
    }
}
