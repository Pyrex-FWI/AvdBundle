<?php

namespace DeejayPoolBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class SvItem
 *
 * @package DeejayPoolBundle\Entity
 * @author Christophe Pyree <yemistikris@hotmail.fr>
 */
class SvItem implements ProviderItemInterface
{
    use ProviderItemTrait;

    const ADVISOR_DIRTY = 'DY';
    const ADVISOR_CLEAN = 'CN';
    const TYPE_ACAPELLA = 'AA';
    const TYPE_BLENDX = 'BX';
    const TYPE_BLENDX_IN = 'BN';
    const TYPE_CLUB = 'CB';
    const TYPE_FUNCKY_MIX = 'FM';
    const TYPE_SE_LYRICS_VIDEO = 'SELV';
    const TYPE_SE_X = 'SEXX';
    const TYPE_XZ_X = 'XZXX';
    const TYPE_SZ_X = 'SZXX';
    const TYPE_TRANSITION = 'TN';
    const TYPE_TRANSITION_X = 'TNXX';
    const TYPE_SINGLE = 'SE';
    const TYPE_SNIPZ = 'SZ';
    const TYPE_TRANSITION_DOWN = 'TNDN';
    const TYPE_TRANSITION_UP = 'TNUP';
    const TYPE_ULTIMIX = 'UX';
    const TYPE_XTENDZ = 'XZ';
    const TYPE_INTRO = 'IO';
    const TYPE_XZ_LYRICS_VIDEO = 'XZLV';
    const TYPE_SYNX_CLUB = 'CBSX';
    const TYPE_SINGLE_SYNX = 'SESX';
    const TYPE_SZ_LYRICS_VIDEO = 'SZLV';
    const TYPE_CB_LYRICS_VIDEO = 'CBLV';
    const TYPE_XTENDZ_SYNX = 'XZSX';
    const TYPE_CB_ALTERNATE_VIDEO = 'CBAV';
    const TYPE_SZ_ALTERNATE_VIDEO = 'SZAV';
    const TYPE_SE_ALTERNATE_VIDEO = 'SEAV';
    const TYPE_XV_ALTERNATE_VIDEO = 'XZAV';

    /**
     * @var
     */
    protected $groupId;
    /**
     * @var
     */
    protected $downloadId;
    /**
     * @var bool
     */
    protected $isHD = false;
    /**
     * @var
     */
    protected $completeVersion;
    /**
     * @var
     */
    protected $videoId;
    /**
     * @var
     */
    protected $videoFile;
    /**
     * @var array
     */
    protected $videoFileProperties = [];
    /**
     * @var bool
     */
    protected $single = false;
    /**
     * @var bool
     */
    protected $xtend = false;
    /**
     * @var bool
     */
    protected $snipz = false;
    /**
     * @var bool
     */
    protected $transitionDown = false;
    /**
     * @var bool
     */
    protected $transitionUp = false;
    /**
     * @var bool
     */
    protected $ultimix = false;
    /**
     * @var bool
     */
    protected $acapella = false;
    /**
     * @var bool
     */
    protected $blendX = false;
    /**
     * @var bool
     */
    protected $blendXIn = false;
    /**
     * @var bool
     */
    protected $sex = false;
    /**
     * @var bool
     */
    protected $club = false;
    /**
     * @var bool
     */
    protected $dirty = false;
    /**
     * @var bool
     */
    protected $clean = false;
    /**
     * @var bool
     */
    protected $funckyMix = false;
    /**
     * @var bool
     */
    protected $lyrics = false;
    /**
     * @var bool
     */
    protected $synX = false;
    /**
     * @var bool
     */
    protected $alerternateVideo = false;
    /**
     * @var bool
     */
    protected $transition = false;
    /**
     * @var bool
     */
    protected $intro = false;
    /**
     * @var bool
     */
    protected $qHD = false;
    /**
     * @var bool
     */
    protected $hd720 = false;
    /**
     * @var bool
     */
    protected $hd1080 = false;
    /**
     * @var bool
     */
    protected $parent = false;
    /**
     * @var
     */
    protected $downloadable;
    /** @var ArrayCollection<SvItem> */
    protected $svItems;
    /** @var   */
    private $durationMode;

    /**
     * SvItem constructor.
     */
    public function __construct()
    {
        $this->relatedGenres = new ArrayCollection();
        $this->svItems = new ArrayCollection();
    }

    /**
     * @param bool $boolValue
     * @return $this
     */
    public function setDownloadable($boolValue)
    {
        $this->downloadable = $boolValue;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownloadable()
    {
        return $this->downloadable;
    }

    /**
     * @return $isParent
     */
    public function isParent()
    {
        return $this->parent;
    }

    /**
     * @param bool $isParent
     *
     * @return $this
     */
    public function setParent($isParent)
    {
        $this->parent = $isParent;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getSvItems()
    {
        return $this->svItems;
    }

    /**
     * @param ArrayCollection $svItems
     *
     * @return $this
     */
    public function setSvItems($svItems)
    {
        $this->svItems = $svItems;

        return $this;
    }

    /**
     * Get trackId.
     *
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set trackId.
     *
     * @param mixed $itemItd
     * @return SvItem
     */
    public function setGroupId($itemItd)
    {
        $this->groupId = $itemItd;

        return $this;
    }

    /**
     * @param SvItem $svItem
     *
     * @return $this
     */
    public function addSvItem($svItem)
    {
        if (!$this->svItems->contains($svItem)) {
            $this->svItems->add($svItem);
        }

        return $this;
    }

    /**
     * @param SvItem $svItem
     *
     * @return $this
     */
    public function removeSvItem($svItem)
    {
        if ($this->svItems->contains($svItem)) {
            $this->svItems->removeElement($svItem);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownloadId()
    {
        return $this->downloadId;
    }

    /**
     * @param mixed $downloadId
     */
    public function setDownloadId($downloadId)
    {
        $this->downloadId = $downloadId;
    }

    /**
     * @param true $true
     *
     * @return $this
     */
    public function setHD($true)
    {
        $this->isHD = $true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isHD()
    {
        return $this->isHD;
    }

    /**
     * Get videoId.
     *
     * @return $videoId
     */
    public function getVideoId()
    {
        return $this->videoId;
    }

    /**
     * Set videoId.
     *
     * @param string $videoId
     *
     * @return SvItem
     */
    public function setVideoId($videoId)
    {
        $this->videoId = $videoId;

        return $this;
    }

    /**
     * Get videoFile.
     *
     * @return $videoFile
     */
    public function getVideoFile()
    {
        return $this->videoFile;
    }

    /**
     * Set videoFile.
     *
     * @param string $videoFile
     *
     * @return SvItem
     */
    public function setVideoFile($videoFile)
    {
        $this->videoFile = strtoupper($videoFile);
        $this->videoFileProperties = (array) explode('_', $this->videoFile);

        return $this;
    }

    /**
     * @return mixed
     */
    public function updateInfoFromVideoFileProperties()
    {
        $propertiesCount = count($this->videoFileProperties);
        if ($propertiesCount === 0) {
            return;
        }

        $this->setTypeFromVideoFileProperties();
        $this->setAdvisorFromVideoFileProperties();
        if ($propertiesCount >= 5) {
            $this->setQualityFromVideoFileProperties();
        }
    }

    /**
     * @return $this
     */
    private function setAdvisorFromVideoFileProperties()
    {
        if (count($this->videoFileProperties) > 1 && in_array($this->videoFileProperties[3], self::getAllowedAdvisors())) {
            if ($this->videoFileProperties[3] === self::ADVISOR_CLEAN) {
                $this->setClean(true);
            }
            if ($this->videoFileProperties[3] === self::ADVISOR_DIRTY) {
                $this->setDirty(true);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function setTypeFromVideoFileProperties()
    {
        if (count($this->videoFileProperties) > 2 && in_array($this->videoFileProperties[2], self::getAllowedTypes())) {
            if ($this->videoFileProperties[2] === self::TYPE_ACAPELLA) {
                $this->setAcapella(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_BLENDX) {
                $this->setBlendX(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_BLENDX_IN) {
                $this->setBlendXIn(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_CLUB) {
                $this->setClub(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SINGLE) {
                $this->setSingle(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SNIPZ) {
                $this->setSnipz(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_TRANSITION_DOWN) {
                $this->setTransitionDown(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_TRANSITION_UP) {
                $this->setTransitionUp(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_XTENDZ) {
                $this->setXtend(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_ULTIMIX) {
                $this->setUltimix(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SE_X) {
                $this->setSex(true);
                $this->setSingle(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_TRANSITION) {
                $this->setTransition(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_FUNCKY_MIX) {
                $this->setFunckyMix(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SE_LYRICS_VIDEO) {
                $this->setLyrics(true);
                $this->setSingle(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_XZ_LYRICS_VIDEO) {
                $this->setLyrics(true);
                $this->setXtend(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SYNX_CLUB) {
                $this->setSynX(true);
                $this->setClub(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_XTENDZ_SYNX) {
                $this->setSynX(true);
                $this->setXtend(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SINGLE_SYNX) {
                $this->setSynX(true);
                $this->setSingle(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SZ_LYRICS_VIDEO) {
                $this->setSnipz(true);
                $this->setLyrics(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_CB_LYRICS_VIDEO) {
                $this->setClub(true);
                $this->setLyrics(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_CB_ALTERNATE_VIDEO) {
                $this->setClub(true);
                $this->setAlerternateVideo(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_XV_ALTERNATE_VIDEO) {
                $this->setXtend(true);
                $this->setAlerternateVideo(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SZ_ALTERNATE_VIDEO) {
                $this->setSnipz(true);
                $this->setAlerternateVideo(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SE_ALTERNATE_VIDEO) {
                $this->setSingle(true);
                $this->setAlerternateVideo(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_INTRO) {
                $this->setIntro(true);
            }
            if ($this->videoFileProperties[2] === self::TYPE_SZ_X) {
                $this->setSnipz(true);
                $this->setSex(true);
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function setQualityFromVideoFileProperties()
    {
        return $this;
    }

    /**
     * Get Xtend.
     *
     * @return bool
     */
    public function isExtend()
    {
        return $this->xtend;
    }

    /**
     * Set Xtends.
     *
     * @param bool $xtend
     *
     * @return SvItem
     */
    public function setXtend($xtend)
    {
        $this->xtend = $xtend;

        return $this;
    }

    /**
     * @return bool
     */
    public function isXtend()
    {
        return $this->xtend;
    }

    /**
     * Get $qHD.
     *
     * @return bool
     */
    public function isQHD()
    {
        return $this->qHD;
    }

    /**
     * Set Dirty.
     *
     * @param bool $qHD
     *
     * @return SvItem
     */
    public function setQHD($qHD)
    {
        $this->qHD = $qHD;

        return $this;
    }

    /**
     * Get $qHD.
     *
     * @return bool
     */
    public function is720()
    {
        return $this->hd720;
    }

    /**
     * Set $hd720.
     *
     * @param bool $hd720
     *
     * @return SvItem
     */
    public function set720($hd720)
    {
        $this->hd720 = $hd720;
        $this->setHD(true);

        return $this;
    }

    /**
     * Get $hd1080.
     *
     * @return bool
     */
    public function is1080()
    {
        return $this->hd1080;
    }

    /**
     * Set $hd1080.
     *
     * @param bool $hd1080
     *
     * @return SvItem
     */
    public function set1080($hd1080)
    {
        $this->hd1080 = $hd1080;
        $this->setHD(true);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompleteVersion()
    {
        return $this->completeVersion;
    }

    /**
     * @param mixed $version
     *
     * @return SvItem
     */
    public function setCompleteVersion($version)
    {
        $this->completeVersion = $version;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDurationMode()
    {
        return $this->durationMode;
    }

    /**
     * @param mixed $durationMode
     *
     * @return SvItem
     */
    public function setDurationMode($durationMode)
    {
        $this->durationMode = $durationMode;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSnipz()
    {
        return $this->snipz;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setSnipz($value)
    {
        $this->snipz = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSingle()
    {
        return $this->single;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setSingle($value)
    {
        $this->single = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDirty()
    {
        return $this->dirty;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setDirty($value)
    {
        $this->dirty = $value;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClean()
    {
        return $this->clean;
    }

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setClean($value)
    {
        $this->clean = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
        $this->svItems = new ArrayCollection();
    }

    /**
     * @return bool
     */
    public function isTransitionDown()
    {
        return $this->transitionDown;
    }

    /**
     * @param bool $transitionDown
     *
     * @return SvItem
     */
    public function setTransitionDown($transitionDown)
    {
        $this->transitionDown = $transitionDown;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTransitionUp()
    {
        return $this->transitionUp;
    }

    /**
     * @param bool $transitionUp
     *
     * @return SvItem
     */
    public function setTransitionUp($transitionUp)
    {
        $this->transitionUp = $transitionUp;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUltimix()
    {
        return $this->ultimix;
    }

    /**
     * @param bool $ultimix
     *
     * @return SvItem
     */
    public function setUltimix($ultimix)
    {
        $this->ultimix = $ultimix;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAcapella()
    {
        return $this->acapella;
    }

    /**
     * @param bool $acapella
     *
     * @return SvItem
     */
    public function setAcapella($acapella)
    {
        $this->acapella = $acapella;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBlendX()
    {
        return $this->blendX;
    }

    /**
     * @param bool $blendX
     *
     * @return SvItem
     */
    public function setBlendX($blendX)
    {
        $this->blendX = $blendX;

        return $this;
    }

    /**
     * @return bool
     */
    public function isBlendXIn()
    {
        return $this->blendXIn;
    }

    /**
     * @param bool $blendXIn
     *
     * @return SvItem
     */
    public function setBlendXIn($blendXIn)
    {
        $this->blendXIn = $blendXIn;

        return $this;
    }

    /**
     * @return bool
     */
    public function isClub()
    {
        return $this->club;
    }

    /**
     * @param bool $club
     *
     * @return SvItem
     */
    public function setClub($club)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSex()
    {
        return $this->sex;
    }

    /**
     * @param bool $sex
     *
     * @return SvItem
     */
    public function setSex($sex)
    {
        $this->sex = $sex;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFunckyMix()
    {
        return $this->funckyMix;
    }

    /**
     * @param bool $funckyMix
     *
     * @return SvItem
     */
    public function setFunckyMix($funckyMix)
    {
        $this->funckyMix = $funckyMix;

        return $this;
    }

    /**
     * @return bool
     */
    public function isLyrics()
    {
        return $this->lyrics;
    }

    /**
     * @param bool $lyrics
     *
     * @return SvItem
     */
    public function setLyrics($lyrics)
    {
        $this->lyrics = $lyrics;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSynX()
    {
        return $this->synX;
    }

    /**
     * @param bool $synX
     *
     * @return SvItem
     */
    public function setSynX($synX)
    {
        $this->synX = $synX;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAlerternateVideo()
    {
        return $this->alerternateVideo;
    }

    /**
     * @param bool $alerternateVideo
     *
     * @return SvItem
     */
    public function setAlerternateVideo($alerternateVideo)
    {
        $this->alerternateVideo = $alerternateVideo;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTransition()
    {
        return $this->transition;
    }

    /**
     * @param bool $transition
     *
     * @return SvItem
     */
    public function setTransition($transition)
    {
        $this->transition = $transition;

        return $this;
    }

    /**
     * @return bool
     */
    public function isIntro()
    {
        return $this->intro;
    }

    /**
     * @param bool $intro
     *
     * @return SvItem
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;

        return $this;
    }

    /**
     * @return array
     */
    public static function getAllowedAdvisors()
    {
        return [
            self::ADVISOR_DIRTY,
            self::ADVISOR_CLEAN,
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedTypes()
    {
        return [
            self::TYPE_SINGLE,
            self::TYPE_XTENDZ,
            self::TYPE_SNIPZ,
            self::TYPE_TRANSITION_DOWN,
            self::TYPE_TRANSITION_UP,
            self::TYPE_ULTIMIX,
            self::TYPE_ACAPELLA,
            self::TYPE_BLENDX,
            self::TYPE_BLENDX_IN,
            self::TYPE_CLUB,
            self::TYPE_SE_X,
            self::TYPE_XZ_X,
            self::TYPE_FUNCKY_MIX,
            self::TYPE_SE_LYRICS_VIDEO,
            self::TYPE_XZ_LYRICS_VIDEO,
            self::TYPE_SYNX_CLUB,
            self::TYPE_SZ_LYRICS_VIDEO,
            self::TYPE_CB_LYRICS_VIDEO,
            self::TYPE_XTENDZ_SYNX,
            self::TYPE_CB_ALTERNATE_VIDEO,
            self::TYPE_SZ_ALTERNATE_VIDEO,
            self::TYPE_SE_ALTERNATE_VIDEO,
            self::TYPE_TRANSITION_X,
            self::TYPE_TRANSITION,
            self::TYPE_SINGLE_SYNX,
            self::TYPE_INTRO,
            self::TYPE_SZ_X,
            self::TYPE_XV_ALTERNATE_VIDEO,
        ];
    }
}
