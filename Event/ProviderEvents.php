<?php

namespace DeejayPoolBundle\Event;


final class ProviderEvents {


    const SESSION_OPENED                = 'session.opened';
    const SESSION_OPEN_ERROR            = 'session.open_error';
    const SESSION_CLOSED                = 'session.closed';

    const ITEMS_POST_GETLIST            = 'session.items.post_getList';
    const ITEMS_PRE_GETLIST             = 'session.items.pre_getList';

    const ITEM_PRE_DOWNLOAD             = 'session.item.pre_download';
    const ITEM_SUCCESS_DOWNLOAD         = 'session.item.success_download';
    const ITEM_ERROR_DOWNLOAD           = 'session.item.error_download';
    const ITEM_DOWNLOAD_SKIPPED         = 'session.item.download_skipped';
    const ITEM_FORCED_DOWNLOAD_SUCCESS  = 'session.item.force_forced_download_success';
    const ITEM_FORCED_DOWNLOAD_ERROR    = 'session.item.force_forced_download_error';

}