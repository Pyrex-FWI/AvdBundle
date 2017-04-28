<?php

namespace DeejayPoolBundle\Provider;

/**
 * Interface SearchablePoolProviderInterface
 *
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 * @package DeejayPoolBundle\Provider
 */
interface SearchablePoolProviderInterface
{
    /**
     * @return []
     */
    public function getAvailableCriteria();

    /**
     * @return mixed
     */
    public function getMaxPage();

    /**
     * @return mixed
     */
    public function getResultCount();

    /**
     * @param array $filters
     * @return mixed
     */
    public function search($filters = []);
}
