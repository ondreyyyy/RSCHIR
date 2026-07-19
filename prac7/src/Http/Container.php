<?php
declare(strict_types=1);

namespace App\Http;

use App\Application\Charts\ChartService;
use App\Application\Localization\UiTextProvider;
use App\Application\Localization\WeatherLocalizer;
use App\Application\Pdf\PdfService;
use App\Application\Preferences\PreferencesService;
use App\Application\User\UserService;
use App\Application\Weather\WeatherService;
use App\Domain\PdfStorage;
use App\Domain\UserRepository;
use App\Domain\WeatherRepository;
use App\Infrastructure\AppConfig;
use App\Infrastructure\ChartWatermark;
use App\Infrastructure\FileSystemPdfStorage;
use App\Infrastructure\PdoFactory;
use App\Infrastructure\PdoUserRepository;
use App\Infrastructure\PdoWeatherRepository;

final class Container
{
    /** @var array<string, object> */
    private array $services = [];

    public function __construct(
        private AppConfig $config = new AppConfig()
    ) {
    }

    public function config(): AppConfig
    {
        return $this->config;
    }

    public function preferences(): PreferencesService
    {
        return $this->shared(PreferencesService::class, fn(): PreferencesService => new PreferencesService());
    }

    public function uiText(): UiTextProvider
    {
        return $this->shared(UiTextProvider::class, fn(): UiTextProvider => new UiTextProvider());
    }

    public function weatherLocalizer(): WeatherLocalizer
    {
        return $this->shared(WeatherLocalizer::class, fn(): WeatherLocalizer => new WeatherLocalizer());
    }

    public function weatherRepository(): WeatherRepository
    {
        return $this->shared(WeatherRepository::class, fn(): WeatherRepository => new PdoWeatherRepository(PdoFactory::get($this->config)));
    }

    public function userRepository(): UserRepository
    {
        return $this->shared(UserRepository::class, fn(): UserRepository => new PdoUserRepository(PdoFactory::get($this->config)));
    }

    public function pdfStorage(): PdfStorage
    {
        return $this->shared(PdfStorage::class, fn(): PdfStorage => new FileSystemPdfStorage($this->config));
    }

    public function weatherService(): WeatherService
    {
        return $this->shared(WeatherService::class, fn(): WeatherService => new WeatherService($this->weatherRepository()));
    }

    public function userService(): UserService
    {
        return $this->shared(UserService::class, fn(): UserService => new UserService($this->userRepository()));
    }

    public function pdfService(): PdfService
    {
        return $this->shared(PdfService::class, fn(): PdfService => new PdfService($this->pdfStorage()));
    }

    public function chartService(): ChartService
    {
        return $this->shared(ChartService::class, fn(): ChartService => new ChartService(new ChartWatermark(), $this->config));
    }

    /**
     * @template T of object
     * @param class-string<T> $id
     * @param callable():T $factory
     * @return T
     */
    private function shared(string $id, callable $factory): object
    {
        if (!isset($this->services[$id])) {
            $this->services[$id] = $factory();
        }

        return $this->services[$id];
    }
}
