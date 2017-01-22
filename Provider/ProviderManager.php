<?php

namespace DeejayPoolBundle\Provider;

/**
 * Class ProviderManager.
 */
class ProviderManager
{
    /** @var array PoolProviderInterface[] */
    private $providers = [];

    /**
     * @param PoolProviderInterface $poolProvider
     */
    public function addProvider(PoolProviderInterface $poolProvider)
    {
        $this->providers[$poolProvider->getName()] = $poolProvider;
    }

    /**
     * @param string $name
     *
     * @throws \UnexpectedValueException
     *
     * @return array|mixed
     */
    public function get($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \UnexpectedValueException(sprintf('%s provider not Found', $name));
        }

        return $this->providers[$name];
    }

    /**
     * @return array
     */
    public function getProviers()
    {
        return $this->providers;
    }
}
