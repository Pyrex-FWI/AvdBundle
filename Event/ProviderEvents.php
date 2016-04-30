<?php

namespace DeejayPoolBundle\Event;

final class ProviderEvents
{
    const SESSION_OPENED = 'provider.session.opened';
    const SESSION_OPEN_ERROR = 'provider.session.open_error';
    const SESSION_CLOSED = 'provider.session.closed';

    const ITEMS_POST_GETLIST = 'provider.items.post_getList';

    const ITEM_PRE_DOWNLOAD = 'provider.item.pre_download';
    const ITEM_SUCCESS_DOWNLOAD = 'provider.item.success_download';
    const ITEM_ERROR_DOWNLOAD = 'provider.item.error_download';

    const SEARCH_ITEM_LOCALY = 'provider.search.item.localy';
}
