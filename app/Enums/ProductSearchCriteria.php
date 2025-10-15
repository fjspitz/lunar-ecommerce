<?php

namespace App\Enums;


enum ProductSearchCriteria: string
{
    case BY_CATEGORY = 'by_category';
    case BY_BRAND = 'by_brand';
    case BY_ASSOCIATION = 'by_association';
    case BY_CHANNEL = 'by_channel';
    case BY_COLLECTION = 'by_collection';
    case BY_TAG = 'by_tag';
}
