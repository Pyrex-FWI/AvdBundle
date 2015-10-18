<?php

/**
 * Created by PhpStorm.
 * User: chpyr
 * Date: 30/08/15
 * Time: 18:45.
 */
namespace DeejayPoolBundle\Serializer\Normalizer;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class FranchiseRecordPoolVideoItemNormalizer extends AbstractNormalizer
{
    const ITEM_VIDEO = 'FranchiseRecordPoolAudioItem';

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param array  $data    data to restore
     * @param string $class   the expected class to instantiate
     * @param string $format  format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return AvdItem
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $frpItem = new FranchisePoolItem();
        $frpItem->setItemId(intval(intval($data['id'])));
        $frpItem->setArtist(strip_tags($data['cell'][2]));
        $frpItem->setTitle(strip_tags($data['cell'][3]));
        $frpItem->addRelatedGenre(trim(strip_tags($data['cell'][4])));
        $frpItem->setReleaseDate(\DateTime::createFromFormat('m/d/Y', $data['cell'][5]));
        $frpItem->setVideo(true);

        return $frpItem;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   Data to denormalize from.
     * @param string $type   The class to which the data should be denormalized.
     * @param string $format The format being deserialized from.
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return in_array($type, [self::ITEM_VIDEO]) && is_array($data);
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param object $object  object to normalize
     * @param string $format  format the normalization result will be encoded as
     * @param array  $context Context options for the normalizer
     *
     * @return array|string|bool|int|float|null
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return;
    }

    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed  $data   Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }
}
