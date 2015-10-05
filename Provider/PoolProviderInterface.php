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
     * This mathod must open new connection on
     * dedicated provider and return true if connection
     * are successfuly openned.
     * @method open
     * @param  string $login    login account
     * @param  string $password password account
     * @return bool             result
     */
    public function open($login, $password);

    /**
     * This method must turn off an openned connection
     * correctly.
     * @method close
     * @return void
     */
    public function close();

    /**
     * [getItems description]
     * @method getItems
     * @param  [type]   $pageNum [description]
     * @return [type]            [description]
     */
    public function getItems($pageNum);

    /**
     * Download a given item.
     * @method downloadItem
     * @param  ProviderItemInterface $item item to download
     * @return void
     */
    public function downloadItem(ProviderItemInterface $item);

    /**
     * Check if item can be downloaded from provider permission
     * @method itemCanBeDownload
     * @param  ProviderItemInterface $item [description]
     * @return [type]                      [description]
     */
    public function itemCanBeDownload(ProviderItemInterface $item);

    public function getName();

    /**
     * @return bool
     */
    public function supportAsyncDownload();
}
