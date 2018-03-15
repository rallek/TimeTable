<?php
/**
 * Almanac.
 *
 * @copyright Ralf Koester (RK)
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @author Ralf Koester <ralf@familie-koester.de>.
 * @link http://k62.de
 * @link http://zikula.org
 * @version Generated by ModuleStudio 1.3.1 (https://modulestudio.de).
 */

namespace RK\AlmanacModule\Base;

/**
 * Events definition base class.
 */
abstract class AbstractAlmanacEvents
{
    /**
     * The rkalmanacmodule.date_post_load event is thrown when dates
     * are loaded from the database.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::postLoad()
     * @var string
     */
    const DATE_POST_LOAD = 'rkalmanacmodule.date_post_load';
    
    /**
     * The rkalmanacmodule.date_pre_persist event is thrown before a new date
     * is created in the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::prePersist()
     * @var string
     */
    const DATE_PRE_PERSIST = 'rkalmanacmodule.date_pre_persist';
    
    /**
     * The rkalmanacmodule.date_post_persist event is thrown after a new date
     * has been created in the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::postPersist()
     * @var string
     */
    const DATE_POST_PERSIST = 'rkalmanacmodule.date_post_persist';
    
    /**
     * The rkalmanacmodule.date_pre_remove event is thrown before an existing date
     * is removed from the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::preRemove()
     * @var string
     */
    const DATE_PRE_REMOVE = 'rkalmanacmodule.date_pre_remove';
    
    /**
     * The rkalmanacmodule.date_post_remove event is thrown after an existing date
     * has been removed from the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::postRemove()
     * @var string
     */
    const DATE_POST_REMOVE = 'rkalmanacmodule.date_post_remove';
    
    /**
     * The rkalmanacmodule.date_pre_update event is thrown before an existing date
     * is updated in the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::preUpdate()
     * @var string
     */
    const DATE_PRE_UPDATE = 'rkalmanacmodule.date_pre_update';
    
    /**
     * The rkalmanacmodule.date_post_update event is thrown after an existing new date
     * has been updated in the system.
     *
     * The event listener receives an
     * RK\AlmanacModule\Event\FilterDateEvent instance.
     *
     * @see RK\AlmanacModule\Listener\EntityLifecycleListener::postUpdate()
     * @var string
     */
    const DATE_POST_UPDATE = 'rkalmanacmodule.date_post_update';
    
}
