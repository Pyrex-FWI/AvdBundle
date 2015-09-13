<?php

namespace DeejayPoolBundle\Provider;


use DeejayPoolBundle\Entity\ProviderItemInterface;

interface PoolProviderInterface
{
    public function open($login, $password);

    public function close();

    public function getItems($pageNum);

    public function downloadItem(ProviderItemInterface $item);

    public function getName();

    /**
     * @return bool
     */
    public function supportAsyncDownload();
}