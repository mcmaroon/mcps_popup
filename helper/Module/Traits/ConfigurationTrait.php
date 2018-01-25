<?php
namespace MCPS\Popup\helper\Module\Traits;

trait ConfigurationTrait
{

    private function getDefaultConfig()
    {
        throw new \LogicException('You must override the getDefaultConfig() method. The method return an array.');
    }

    public final function getConfig()
    {
        $defaultConfig = $this->getDefaultConfig();

        if (!$dbConfig = \unserialize(\Configuration::get(\strtoupper($this->name)))) {
            return $defaultConfig;
        }

        return \array_merge($defaultConfig, $dbConfig);
    }

    public final function setConfig(array $config)
    {
        $defaultConfig = $this->getDefaultConfig();
        foreach ($defaultConfig as $defaultConfigKey => $defaultConfigValue) {
            if (\array_key_exists($defaultConfigKey, $config)) {
                $defaultType = gettype($defaultConfigValue);
                if (!\settype($config[$defaultConfigKey], $defaultType)) {
                    $config[$defaultConfigKey] = $defaultConfigValue;
                }
            }
        }
        \Configuration::updateValue(\strtoupper($this->name), \serialize($config));
    }
}
