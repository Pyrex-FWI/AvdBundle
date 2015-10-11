<?php

namespace DeejayPoolBundle\Provider;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * SearchablePoolProviderInterface
 */
interface SearchablePoolProviderInterface
{
    //public function getItemsBy($queryParameters = [], $limit = null);
    
    /**
     * @return []
     */
    public function getAvailableCriteria(); 

    public function getMaxPage();
    public function getResultCount();
    /** @return SearchablePoolProviderInterface */
    public function search($filters = []);
}
