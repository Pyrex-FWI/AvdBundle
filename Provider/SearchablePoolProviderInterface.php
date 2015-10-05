<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\ProviderItemInterface;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * SearchablePoolProviderInterface
 */
interface SearchablePoolProviderInterface
{
    public function getItemsBy($queryParameters = [], $limit = null);
    
}
