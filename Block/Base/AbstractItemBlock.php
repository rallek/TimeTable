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

namespace RK\AlmanacModule\Block\Base;

use Symfony\Component\HttpKernel\Controller\ControllerReference;
use Zikula\BlocksModule\AbstractBlockHandler;
use RK\AlmanacModule\Block\Form\Type\ItemBlockType;

/**
 * Generic item detail block base class.
 */
abstract class AbstractItemBlock extends AbstractBlockHandler
{
    /**
     * Display the block content.
     *
     * @param array $properties The block properties
     *
     * @return string
     */
    public function display(array $properties = [])
    {
        // only show block content if the user has the required permissions
        if (!$this->hasPermission('RKAlmanacModule:ItemBlock:', "$properties[title]::", ACCESS_OVERVIEW)) {
            return '';
        }
    
        // set default values for all params which are not properly set
        $defaults = $this->getDefaults();
        $properties = array_merge($defaults, $properties);
    
        if (null === $properties['id'] || empty($properties['id'])) {
            return '';
        }
    
        $controllerHelper = $this->get('rk_almanac_module.controller_helper');
        $contextArgs = ['name' => 'detail'];
        if (!isset($properties['objectType']) || !in_array($properties['objectType'], $controllerHelper->getObjectTypes('block', $contextArgs))) {
            $properties['objectType'] = $controllerHelper->getDefaultObjectType('block', $contextArgs);
        }
    
        $controllerReference = new ControllerReference('RKAlmanacModule:External:display', $this->getDisplayArguments($properties), ['template' => $properties['customTemplate']]);
    
        return $this->get('fragment.handler')->render($controllerReference, 'inline', []);
    }
    
    /**
     * Returns common arguments for displaying the selected object using the external controller.
     *
     * @param array $properties The block properties
     *
     * @return array Display arguments
     */
    protected function getDisplayArguments(array $properties = [])
    {
        return [
            'objectType' => $properties['objectType'],
            'id' => $properties['id'],
            'source' => 'block',
            'displayMode' => 'embed'
        ];
    }
    
    /**
     * Returns the fully qualified class name of the block's form class.
     *
     * @return string Template path
     */
    public function getFormClassName()
    {
        return ItemBlockType::class;
    }
    
    /**
     * Returns any array of form options.
     *
     * @return array Options array
     */
    public function getFormOptions()
    {
        $objectType = 'date';
    
        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->attributes->has('blockEntity')) {
            $blockEntity = $request->attributes->get('blockEntity');
            if (is_object($blockEntity) && method_exists($blockEntity, 'getProperties')) {
                $blockProperties = $blockEntity->getProperties();
                if (isset($blockProperties['objectType'])) {
                    $objectType = $blockProperties['objectType'];
                } else {
                    // set default options for new block creation
                    $blockEntity->setProperties($this->getDefaults());
                }
            }
        }
    
        return [
            'object_type' => $objectType
        ];
    }
    
    /**
     * Returns the template used for rendering the editing form.
     *
     * @return string Template path
     */
    public function getFormTemplate()
    {
        return '@RKAlmanacModule/Block/item_modify.html.twig';
    }
    
    /**
     * Returns default settings for this block.
     *
     * @return array The default settings
     */
    protected function getDefaults()
    {
        return [
            'objectType' => 'date',
            'id' => null,
            'template' => 'item_display.html.twig',
            'customTemplate' => null
        ];
    }
}
