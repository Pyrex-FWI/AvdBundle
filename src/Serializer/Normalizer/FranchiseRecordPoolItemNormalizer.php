<?php

namespace DeejayPoolBundle\Serializer\Normalizer;

use DeejayPoolBundle\Entity\AvdItem;
use DeejayPoolBundle\Entity\FranchisePoolItem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * Class FranchiseRecordPoolItemNormalizer
 *
 * @package DeejayPoolBundle\Serializer\Normalizer
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class FranchiseRecordPoolItemNormalizer extends AbstractNormalizer
{
    const ITEM_AUDIO = 'FranchiseRecordPoolItem';
    const ITEM_VIDEO = 'FranchiseRecordPoolAudioItem';

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param array  $data    data to restore
     * @param string $class   the expected class to instantiate
     * @param string $format  format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return FranchisePoolItem
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $frpItem = new FranchisePoolItem();
        $frpItem->setItemId(intval(intval($data['id'])));
        $frpItem->setArtist($this->parseHtmlText($data['cell'][1], 'a.popup-artist'));
        $frpItem->setTitle($this->parseHtmlText($data['cell'][1], 'a.popup-song'));
        $frpItem->addRelatedGenre(trim(strip_tags($data['cell'][4])));
        $frpItem->setBpm(intval($data['cell'][5]));
        $frpItem->setReleaseDate(\DateTime::createFromFormat('m/d/Y', $data['cell'][6]));
        $frpItem->setVersion(null);
        $frpItem->setAudio(true);

        return $frpItem;
    }

    /**
     * @param string $html
     * @param null   $filter
     * @return string
     */
    public function parseHtmlText($html, $filter = null)
    {
        $crawler = new Crawler($html);

        if ($filter) {
            $node = $crawler->filter($filter);
            if ($node->count() > 0 && $crawler->text()) {
                return $node->text();
            }
        }

        if ($crawler->text()) {
            return $crawler->text();
        }
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
        return in_array($type, [self::ITEM_AUDIO, self::ITEM_VIDEO]) && is_array($data);
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
    public function normalize($object, $format = null, array $context = [])
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
