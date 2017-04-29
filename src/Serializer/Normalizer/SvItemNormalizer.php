<?php

namespace DeejayPoolBundle\Serializer\Normalizer;

use DeejayPoolBundle\Entity\SvGroup;
use DeejayPoolBundle\Entity\SvItem;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

/**
 * @author Pyrex-FWI <yemistikris@hotmail.fr>
 *
 * SvItemNormalizer
 */
class SvItemNormalizer extends AbstractNormalizer
{
    const SVITEM = 'SvItem';
    const QUALITY_QHD = 1;          //groupId like "17991_qHD"
    const QUALITY_HD_720 = 2;       //groupId like "17988_HD720"
    const QUALITY_HD_1080 = 3;      //groupId like "18018_HD1080"

    /**
     * Denormalizes data back into an object of the given class.
     *
     * @param array  $data    data to restore
     * @param string $class   the expected class to instantiate
     * @param string $format  format the given data was extracted from
     * @param array  $context options available to the denormalizer
     *
     * @return SvItem
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $svGroup = new SvItem();
        $svGroup->setGroupId((int) $data['groupId']);
        $svGroup->setArtist($data['artist']);
        $svGroup->setTitle($data['title']);
        $svGroup->setBpm((int) $data['bpm']);
        $svGroup->addRelatedGenre($data['genre']);
        $svGroup->setReleaseDate((new \DateTime())->setTimestamp($this->extractReleaseDate($data['date'])));
        if (self::QUALITY_HD_720 === $data['quality']) {
            $svGroup->set720(true);
        }
        if (self::QUALITY_HD_1080 === $data['quality']) {
            $svGroup->set1080(true);
        }
        if (self::QUALITY_QHD === $data['quality']) {
            $svGroup->setQHD(true);
        }

        foreach ((array) $data['videos'] as $videoArray) {
            $svItem = clone $svGroup;
            $svItem->setVideoId($videoArray['videoId']);
            if ($videoFilePart = explode('.', $videoArray['video_file'])) {
                $svItem->setVideoFile($videoFilePart[0]);
                $svItem->updateInfoFromVideoFileProperties();
            }
            $svItem->setDownloaded((bool) $videoArray['downloaded']);
            $completeVersion = $this->extractCompleteVersion($videoArray);
            $svItem->setVersion($completeVersion);
            $svItem->setParent(false);
            $svItem->setItemId(sprintf('%s_%s', $svGroup->getGroupId(), $svItem->getVideoId()));
            $svGroup->addSvItem($svItem);
        }
        $svGroup->setParent(true);

        return $svGroup;
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
        return self::SVITEM === $type && is_array($data);
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


    /**
     * @param array $videoArray
     * @return string
     */
    public function extractCompleteVersion(array $videoArray)
    {
        $duration = $this->exactDurationVersion($videoArray['title']);
        if ($duration) {
            return sprintf('%s/%s', $duration, $this->exactContentVersion($videoArray['title']));
        } else {
            return $this->exactContentVersion($videoArray['title']);
        }
    }

    /**
     * [exactDurationVersion description].
     *
     * @method exactDurationVersion
     *
     * @param [type] $title [description]
     *
     * @return [type] [description]
     */
    private function exactDurationVersion($title)
    {
        if (preg_match('/(?<version>single|xtendz|snipz)/i', $title, $matches) > 0) {
            return $matches['version'];
        }
    }

    /**
     * [exactContentVersion description].
     *
     * @method exactContentVersion
     *
     * @param [type] $title [description]
     *
     * @return [type] [description]
     */
    private function exactContentVersion($title)
    {
        if (preg_match('/(?<version>clean|dirty)$/i', $title, $matches) > 0) {
            return $matches['version'];
        }
    }

    /**
     * Extract timestamp from microtime timestamp.
     *
     * @method extractReleaseDate
     *
     * @param string $date string like /Date(12345678901234)/
     *
     * @return int 1234567890
     */
    private function extractReleaseDate($date)
    {
        if (preg_match('#^/Date\((?<date>\d*)\)/$#i', $date, $matches) > 0) {
            return intval(substr($matches['date'], 0, 10));
        }

        return 0;
    }
}
