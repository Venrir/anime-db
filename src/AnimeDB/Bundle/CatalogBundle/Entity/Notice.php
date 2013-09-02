<?php
/**
 * AnimeDB package
 *
 * @package   AnimeDB
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDB\Bundle\CatalogBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Notice for user
 *
 * @ORM\Entity
 * @ORM\Table(name="notice", indexes={
 *   @ORM\Index(name="idx_notice_show", columns={"date_closed", "date_created"})
 * })
 * @IgnoreAnnotation("ORM")
 *
 * @package AnimeDB\Bundle\CatalogBundle\Entity
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
class Notice
{
    /**
     * Status notice created
     *
     * @var integer
     */
    const STATUS_CREATED = 0;

    /**
     * Status notice shown
     *
     * @var integer
     */
    const STATUS_SHOWN = 1;

    /**
     * Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @var integer
     */
    protected $id;

    /**
     * Message
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $message;

    /**
     * Date closed notice
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Assert\DateTime()
     *
     * @var \DateTime|null
     */
    protected $date_closed = null;

    /**
     * Date created notice
     *
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Lifetime notice
     *
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="integer", message="The value {{ value }} is not a valid {{ type }}.")
     *
     * @var string
     */
    protected $lifetime;

    /**
     * Status
     *
     * @ORM\Column(type="integer")
     * @Assert\Choice(callback = "getStatuses")
     *
     * @var integer
     */
    protected $status = self::STATUS_CREATED;

    /**
     * Construct
     */
    public function __construct()
    {
        $this->date_created = new \DateTime();
    }

    /**
     * Get supported statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [self::STATUS_CREATED, self::STATUS_SHOWN];
    }

    /**
     * Notice shown
     */
    public function shown()
    {
        if (is_null($this->end)) {
            $this->end = new \DateTime();
            $this->end->modify('+'.$this->lifetime.' seconds');
        }
        $this->status = self::STATUS_SHOWN;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return \AnimeDB\Bundle\CatalogBundle\Entity\Notice
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set date_closed
     *
     * @param \DateTime $date_closed
     *
     * @return \AnimeDB\Bundle\CatalogBundle\Entity\Notice
     */
    public function setDateClosed(\DateTime $date_closed)
    {
        $this->date_closed = clone $date_closed;
        return $this;
    }

    /**
     * Get date_closed
     *
     * @return \DateTime 
     */
    public function getDateClosed()
    {
        return $this->date_closed;
    }

    /**
     * Get date_created
     *
     * @return \DateTime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set lifetime
     *
     * @param integer $lifetime
     *
     * @return \AnimeDB\Bundle\CatalogBundle\Entity\Notice
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
        return $this;
    }

    /**
     * Get lifetime
     *
     * @return integer 
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return \AnimeDB\Bundle\CatalogBundle\Entity\Notice
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }
}