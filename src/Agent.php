<?php

namespace Dipantry\Analytics;

use Detection\MobileDetect;
use Dipantry\Analytics\Traits\Devices;

class Agent extends MobileDetect
{
    use Devices;

    /**
     * Get accept languages
     *
     * @param string $acceptLanguage
     * @return array
     */
    public function languages(string $acceptLanguage = null)
    {
        if ($acceptLanguage === null) {
            $acceptLanguage = $this->getHttpHeader('HTTP_ACCEPT_LANGUAGE');
        }

        if (!$acceptLanguage) {
            return [];
        }

        $languages = [];

        // Parse accept language string
        foreach (explode(',', $acceptLanguage) as $piece) {
            $parts = explode(';', $piece);
            $language = strtolower($parts[0]);
            $priority = empty($parts[1]) ? 1. : floatval(str_replace('q=', '', $parts[1]));

            $languages[$language] = $priority;
        }

        // Sort languages by priority
        arsort($languages);

        return array_keys($languages);
    }

    /**
     * Get the browser name.
     * @param string|null $userAgent
     * @return string|bool
     */
    public function browser(string $userAgent = null): bool|string
    {
        return $this->findDetectionRulesAgainstUA(static::getBrowsers(), $userAgent);
    }

    /**
     * Match a detection rule and return the matched key
     *
     * @param array $rules
     * @param string|null $userAgent
     * @return string|bool
     */
    protected function findDetectionRulesAgainstUA(array $rules, string $userAgent = null): bool|string
    {
        // Loop given rules
        foreach ($rules as $key => $regex) {
            if (empty($regex)) {
                continue;
            }

            // Check match
            if ($this->match($regex, $userAgent)) {
                return $key ?: reset($this->matchesArray);
            }
        }

        return false;
    }

    /**
     * Get the platform name
     * @param string|null $userAgent
     * @return string|bool
     */
    public function platform(?string $userAgent = null): bool|string
    {
        return $this->findDetectionRulesAgainstUA(static::getPlatforms(), $userAgent);
    }

    /**
     * Get the device name
     * @param string|null $userAgent
     * @return string|bool
     */
    public function device(?string $userAgent = null): bool|string
    {
        $rules = static::mergeRules(
            static::getDesktopDevices(),
            static::getPhoneDevices(),
            static::getTabletDevices(),
            static::getUtilities()
        );

        return $this->findDetectionRulesAgainstUA($rules, $userAgent);
    }

    /**
     * Get the robot name.
     * @param string|null $userAgent
     * @return string|bool
     */
    public function robot(?string $userAgent = null): bool|string
    {
        if ($this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent)) {
            return ucfirst($this->getCrawlerDetect()->getMatches());
        }

        return false;
    }

    /**
     * Get the device type
     * @param null $userAgent
     * @param null $httpHeaders
     * @return string
     */
    public function deviceType($userAgent = null, $httpHeaders = null): string
    {
        if ($this->isDesktop($userAgent, $httpHeaders)) {
            return 'desktop';
        } elseif ($this->isPhone($userAgent, $httpHeaders)) {
            return 'phone';
        } elseif ($this->isTablet($userAgent, $httpHeaders)) {
            return 'tablet';
        } elseif ($this->isRobot($userAgent)) {
            return 'robot';
        }

        return 'other';
    }

    /**
     * Check if the device is a desktop
     * @param string|null $userAgent deprecated
     * @param array|null $httpHeaders deprecated
     * @return bool
     */
    public function isDesktop(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        // Check specifically for cloudfront headers if the useragent === 'Amazon CloudFront'
        if ($this->getUserAgent() === 'Amazon CloudFront') {
            $cfHeaders = $this->getCfHeaders();

            if (array_key_exists('HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER', $cfHeaders)) {
                return $cfHeaders['HTTP_CLOUDFRONT_IS_DESKTOP_VIEWER'] === 'true';
            }
        }

        return !$this->isMobile($userAgent, $httpHeaders) &&
            !$this->isTablet($userAgent, $httpHeaders) &&
            !$this->isRobot($userAgent);
    }

    /**
     * Check if device is a robot.
     * @param string|null $userAgent
     * @return bool
     */
    public function isRobot(string $userAgent = null)
    {
        return $this->getCrawlerDetect()->isCrawler($userAgent ?: $this->userAgent);
    }

    /**
     * Check if the device is a mobile phone
     * @param string|null $userAgent deprecated
     * @param array|null $httpHeaders deprecated
     * @return bool
     */
    public function isPhone(?string $userAgent = null, ?array $httpHeaders = null): bool
    {
        return $this->isMobile($userAgent, $httpHeaders) && !$this->isTablet($userAgent, $httpHeaders);
    }

    /**
     * Get the version of the device
     *
     * @param $propertyName
     * @param $type
     * @return float|bool|string
     */
    public function version($propertyName, $type = self::VERSION_TYPE_STRING): float|bool|string
    {
        if (empty($propertyName)) {
            return false;
        }

        // set the $type to the default if we don't recognize the type
        if ($type !== self::VERSION_TYPE_STRING && $type !== self::VERSION_TYPE_FLOAT) {
            $type = self::VERSION_TYPE_STRING;
        }

        $properties = self::getProperties();

        // Check if the property exists in the properties array
        if (true === isset($properties[$propertyName])) {

            // Prepare the pattern to be matched
            // Make sure we always deal with an array (string is converted)
            $properties[$propertyName] = (array)$properties[$propertyName];

            foreach ($properties[$propertyName] as $propertyMatchString) {
                if (is_array($propertyMatchString)) {
                    $propertyMatchString = implode('|', $propertyMatchString);
                }

                $propertyPattern = str_replace('[VER]', self::VER, $propertyMatchString);

                preg_match(sprintf('#%s#is', $propertyPattern), $this->userAgent, $match);

                if (false === empty($match[1])) {
                    $version = ($type === self::VERSION_TYPE_FLOAT ? $this->prepareVersionNo($match[1]) : $match[1]);

                    return $version;
                }
            }
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $arguments)
    {
        // Make sure the name starts with `is`, otherwise
        if (!str_starts_with($name, 'is')) {
            throw new \BadMethodCallException("No such method exists: $name");
        }

        $key = substr($name, 2);
        $this->matchUAAgainstKey($key);
    }
}