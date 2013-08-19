<?php
/**
 * AnimeDB package
 *
 * @package   AnimeDB
 * @author    Peter Gribanov <info@peter-gribanov.ru>
 * @copyright Copyright (c) 2011, Peter Gribanov
 * @license   http://opensource.org/licenses/GPL-3.0 GPL v3
 */

namespace AnimeDB\Bundle\CatalogBundle\Service\Plugin\Filler;

use AnimeDB\Bundle\CatalogBundle\Service\Plugin\PluginInterface;

/**
 * Plugin filler interface
 * 
 * @package AnimeDB\Bundle\CatalogBundle\Service\Plugin\Filler
 * @author  Peter Gribanov <info@peter-gribanov.ru>
 */
interface FillerInterface extends PluginInterface
{
    /**
     * Fill item from source
     *
     * @param string $source
     *
     * @return \AnimeDB\Bundle\CatalogBundle\Entity\Item|null
     */
    public function fill($source);
}