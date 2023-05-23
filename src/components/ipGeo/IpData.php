<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo;

class IpData
{
    private ?float $lat = null;
    private ?float $lon = null;
    private ?string $isp = null;
    private ?string $city = null;
    private ?string $error = null;
    private ?string $country = null;
    private ?string $timezone = null;
    private ?string $regionName = null;
    private ?string $countryCode = null;

    /**
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return IpData
     */
    public function setError(?string $error): IpData
    {
        $this->error = $error;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimezone(): ?string
    {
        return $this->timezone;
    }

    /**
     * @param string|null $timezone
     * @return IpData
     */
    public function setTimezone(?string $timezone): IpData
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @param string|null $countryCode
     * @return IpData
     */
    public function setCountryCode(?string $countryCode): IpData
    {
        if ($countryCode) {
            $this->countryCode = strtolower($countryCode);
        }
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIsp(): ?string
    {
        return $this->isp;
    }

    /**
     * @param string|null $isp
     * @return IpData
     */
    public function setIsp(?string $isp): IpData
    {
        $this->isp = $isp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountry(): ?string
    {
        return $this->country;
    }

    /**
     * @param string|null $country
     * @return IpData
     */
    public function setCountry(?string $country): IpData
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegionName(): ?string
    {
        return $this->regionName;
    }

    /**
     * @param string|null $regionName
     * @return IpData
     */
    public function setRegionName(?string $regionName): IpData
    {
        $this->regionName = $regionName;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return IpData
     */
    public function setCity(?string $city): IpData
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLat(): ?float
    {
        return $this->lat;
    }

    /**
     * @param float|null $lat
     * @return IpData
     */
    public function setLat(?float $lat): IpData
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLon(): ?float
    {
        return $this->lon;
    }

    /**
     * @param float|null $lon
     * @return IpData
     */
    public function setLon(?float $lon): IpData
    {
        $this->lon = $lon;
        return $this;
    }

    public function getFlag(): string
    {
        $basePath = __DIR__ . '/flags';
        if ($this->countryCode) {
            $code = strtolower(explode('_', $this->countryCode)[0]);
            $path = "$basePath/$code.png";
            if (is_file($path)) {
                return $path;
            }
        }
        return "$basePath/clear.png";
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'lat' => $this->lat,
            'lon' => $this->lon,
            'isp' => $this->isp,
            'city' => $this->city,
            'error' => $this->error,
            'country' => $this->country,
            'timezone' => $this->timezone,
            'regionName' => $this->regionName,
            'countryCode' => $this->countryCode,
        ];
    }

    public function __serialize()
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function getCurrentTime(): \DateTime
    {
        $date = new \DateTime();
        if ($this->timezone) {
            $date->setTimezone(new \DateTimeZone($this->timezone));
        }
        return $date;
    }
}
