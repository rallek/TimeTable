<?php
/**
 * Almanac.
 *
 * @copyright Ralf Koester (RK)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Ralf Koester <ralf@familie-koester.de>.
 * @link http://k62.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio (https://modulestudio.de).
 */

namespace RK\AlmanacModule\Entity;

use RK\AlmanacModule\Entity\Base\AbstractDateCategoryEntity as BaseEntity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Entity extension domain class storing date categories.
 *
 * This is the concrete category class for date entities.
 * @ORM\Entity(repositoryClass="\RK\AlmanacModule\Entity\Repository\DateCategoryRepository")
 * @ORM\Table(name="rk_alma_date_category",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="cat_unq", columns={"registryId", "categoryId", "entityId"})
 *     }
 * )
 */
class DateCategoryEntity extends BaseEntity
{
    // feel free to add your own methods here
}
