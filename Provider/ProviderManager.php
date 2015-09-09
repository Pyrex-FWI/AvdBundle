<?php
/**
 * Date: 06/09/15
 * Time: 11:56
 */

namespace DeejayPoolBundle\Provider;


class ProviderManager
{
    /** @var array PoolProviderInterface[] */
    private $providers = [];

    public function addProvider(PoolProviderInterface $poolProvider)
    {
        $this->providers[$poolProvider->getName()] = $poolProvider;
    }

    /**
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function get($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \Exception(sprintf('%s provider not Found', $name));
        }

        return $this->providers[$name];
    }

    public function getProviers()
    {
        return $this->providers;
    }
}