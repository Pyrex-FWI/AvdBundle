<?php

/**
 * Created by PhpStorm.
 * User: chpyr
 * Date: 30/08/15
 * Time: 18:45.
 */

namespace DeejayPoolBundle\Serializer\Normalizer;

use DeejayPoolBundle\Entity\AvdItem;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class AvItemNormalizer extends AbstractNormalizer
{
    const AVITEM = 'AvItem';

    protected $properties = [];

    /**
     * @param $properties []
     */
    public function __construct($properties)
    {
        $this->properties = $properties;
        parent::__construct();
    }

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
        $data = array_map('trim', $data);
        $avdItem = new AvdItem();
        $avdItem->setItemId(intval($data[0]));
        $avdItem->setTitle(explode('::', $data[1])[0]);
        $version = array_merge([0 => '', 1 => '', 2 => ''], explode('::', $data[1]));
        $avdItem->setVersion(trim($version[1]).'-'.trim($version[2]));
        $avdItem->setArtist($data[2]);
        $genres = explode(',', $data[3]);

        foreach ($genres as $genre) {
            $avdItem->addRelatedGenre(trim($genre));
        }
        $avdItem->setBpm(intval($data[4]));
        $avdItem->setDownloadId(intval($data[12]));
        if (intval($data[12]) > 0) {
            $avdItem->setDownloaded(true);
        }
        $avdItem->setReleaseDate(\DateTime::createFromFormat('m/d/Y', $data[5]));
        $avdItem->setHD(boolval($data[7]));

        return $avdItem;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed  $data   data to denormalize from
     * @param string $type   the class to which the data should be denormalized
     * @param string $format the format being deserialized from
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return self::AVITEM === $type && is_array($data);
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
     * @param mixed  $data   data to normalize
     * @param string $format the format being (de-)serialized from or into
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return false;
    }
}
