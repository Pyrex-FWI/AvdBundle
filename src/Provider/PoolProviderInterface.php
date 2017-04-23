<?php

namespace DeejayPoolBundle\Provider;

use DeejayPoolBundle\Entity\ProviderItemInterface;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * PoolProviderInterface
 */
interface PoolProviderInterface
{
    /**
     * This method must open new connection on
     * dedicated provider and return true if connection
     * is successfully opened.
     *
     * @method open
     *
     * @param string $login    login account
     * @param string $password password account
     *
     * @return bool result
     */
    public function open($login = null, $password = null);

    /**
     * This method must turn off an openned connection
     * correctly.
     *
     * @method close
     */
    public function close();

    /**
     * [getItems description].
     *
     * @method getItems
     *
     * @param [type] $pageNum [description]
     * @param [type] $filter  [description]
     *
     * @return [type] [description]
     */
    public function getItems($pageNum, $filter = []);

    /**
     * Download a given item.
     *
     * @method downloadItem
     *
     * @param ProviderItemInterface $item item to download
     */
    public function downloadItem(ProviderItemInterface $item);

    /**
     * Check if item can be downloaded from provider permission.
     *
     * @method itemCanBeDownload
     *
     * @param ProviderItemInterface $item [description]
     *
     * @return [type] [description]
     */
    public function itemCanBeDownload(ProviderItemInterface $item);

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @return bool
     */
    public function supportAsyncDownload();
}
