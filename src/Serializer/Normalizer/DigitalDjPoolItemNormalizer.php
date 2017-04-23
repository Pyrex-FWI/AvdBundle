<?php

namespace DeejayPoolBundle\Serializer\Normalizer;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\DdpItem;
use DigitalDjPoolBundle\Exception\DownloadLinkNotFoundException;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Class DigitalDjPoolItemNormalizer
 *
 * @package DeejayPoolBundle\Serializer\Normalizer
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class DigitalDjPoolItemNormalizer extends AbstractNormalizer
{
    const DDPITEM = 'DdpItem';

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param array  $dataRaw data to restore
     * @param string $class   the expected class to instantiate
     * @param string $format  format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @throws DownloadLinkNotFoundException
     *
     * @return AvdItem
     */
    public function denormalize($dataRaw, $class, $format = null, array $context = array())
    {
        $data = new Crawler($dataRaw);
        $ddpItem = new DdpItem();

        if ($data->filter('a.ddjp-download')->count() === 0 || $data->filter('a.ddjp-download')->attr('href') == '#') {
            throw new DownloadLinkNotFoundException();
        }
        $ddpItem->setDownloadlink($data->filter('a.ddjp-download')->attr('href'));
        if ($data->filter('input.hid-song-id')->count()) {
            $ddpItem->setItemId($data->filter('input.hid-song-id')->attr('value'));
        }

        if ($data->filter('input.hid-song-artist')->count()) {
            $ddpItem->setArtist($data->filter('input.hid-song-artist')->attr('value'));
        }

        if ($data->filter('input.hid-song-title')->count()) {
            $ddpItem->setTitle($data->filter('input.hid-song-title')->attr('value'));
        }

        if ($data->filter('input.hid-song-version')->count()) {
            $ddpItem->setVersion($data->filter('input.hid-song-version')->attr('value'));
        }

        if ($data->filter('.col-bpm')->count()) {
            $ddpItem->setBpm($data->filter('.col-bpm')->attr('value'));
        }

        $ddpItem->setReleaseDate(new \DateTime());

        return $ddpItem;
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
        return self::DDPITEM === $type && is_string($data);
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
