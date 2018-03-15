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

namespace RK\AlmanacModule\Helper\Base;

use Psr\Log\LoggerInterface;
use Symfony\Component\Workflow\Registry;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\Doctrine\EntityAccess;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\UsersModule\Api\ApiInterface\CurrentUserApiInterface;
use RK\AlmanacModule\Entity\Factory\EntityFactory;
use RK\AlmanacModule\Helper\ListEntriesHelper;

/**
 * Helper base class for workflow methods.
 */
abstract class AbstractWorkflowHelper
{
    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var Registry
     */
    protected $workflowRegistry;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var PermissionApiInterface
     */
    protected $permissionApi;

    /**
     * @var CurrentUserApiInterface
     */
    protected $currentUserApi;

    /**
     * @var EntityFactory
     */
    protected $entityFactory;

    /**
     * @var ListEntriesHelper
     */
    protected $listEntriesHelper;

    /**
     * WorkflowHelper constructor.
     *
     * @param TranslatorInterface     $translator        Translator service instance
     * @param Registry                $registry          Workflow registry service instance
     * @param LoggerInterface         $logger            Logger service instance
     * @param PermissionApiInterface  $permissionApi     PermissionApi service instance
     * @param CurrentUserApiInterface $currentUserApi    CurrentUserApi service instance
     * @param EntityFactory           $entityFactory     EntityFactory service instance
     * @param ListEntriesHelper       $listEntriesHelper ListEntriesHelper service instance
     *
     * @return void
     */
    public function __construct(
        TranslatorInterface $translator,
        Registry $registry,
        LoggerInterface $logger,
        PermissionApiInterface $permissionApi,
        CurrentUserApiInterface $currentUserApi,
        EntityFactory $entityFactory,
        ListEntriesHelper $listEntriesHelper
    ) {
        $this->translator = $translator;
        $this->workflowRegistry = $registry;
        $this->logger = $logger;
        $this->permissionApi = $permissionApi;
        $this->currentUserApi = $currentUserApi;
        $this->entityFactory = $entityFactory;
        $this->listEntriesHelper = $listEntriesHelper;
    }

    /**
      * This method returns a list of possible object states.
      *
      * @return array List of collected state information
      */
     public function getObjectStates()
     {
         $states = [];
         $states[] = [
             'value' => 'initial',
             'text' => $this->translator->__('Initial'),
             'ui' => 'danger'
         ];
         $states[] = [
             'value' => 'deferred',
             'text' => $this->translator->__('Deferred'),
             'ui' => 'danger'
         ];
         $states[] = [
             'value' => 'waiting',
             'text' => $this->translator->__('Waiting'),
             'ui' => 'warning'
         ];
         $states[] = [
             'value' => 'approved',
             'text' => $this->translator->__('Approved'),
             'ui' => 'success'
         ];
         $states[] = [
             'value' => 'suspended',
             'text' => $this->translator->__('Suspended'),
             'ui' => 'primary'
         ];
         $states[] = [
             'value' => 'archived',
             'text' => $this->translator->__('Archived'),
             'ui' => 'info'
         ];
         $states[] = [
             'value' => 'trashed',
             'text' => $this->translator->__('Trashed'),
             'ui' => 'danger'
         ];
         $states[] = [
             'value' => 'deleted',
             'text' => $this->translator->__('Deleted'),
             'ui' => 'danger'
         ];
    
         return $states;
     }
    
    /**
     * This method returns information about a certain state.
     *
     * @param string $state The given state value
     *
     * @return array|null The corresponding state information
     */
    public function getStateInfo($state = 'initial')
    {
        $result = null;
        $stateList = $this->getObjectStates();
        foreach ($stateList as $singleState) {
            if ($singleState['value'] != $state) {
                continue;
            }
            $result = $singleState;
            break;
        }
    
        return $result;
    }
    
    /**
     * Retrieve the available actions for a given entity object.
     *
     * @param EntityAccess $entity The given entity instance
     *
     * @return array List of available workflow actions
     */
    public function getActionsForObject(EntityAccess $entity)
    {
        $workflow = $this->workflowRegistry->get($entity);
        $wfActions = $workflow->getEnabledTransitions($entity);
        $currentState = $entity->getWorkflowState();
    
        $actions = [];
        foreach ($wfActions as $action) {
            $actionId = $action->getName();
            $actions[$actionId] = [
                'id' => $actionId,
                'title' => $this->getTitleForAction($currentState, $actionId),
                'buttonClass' => $this->getButtonClassForAction($actionId)
            ];
        }
    
        return $actions;
    }
    
    /**
     * Returns a translatable title for a certain action.
     *
     * @param string $currentState Current state of the entity
     * @param string $actionId     Id of the treated action
     *
     * @return string The action title
     */
    protected function getTitleForAction($currentState, $actionId)
    {
        $title = '';
        switch ($actionId) {
            case 'defer':
                $title = $this->translator->__('Defer');
                break;
            case 'submit':
                $title = $this->translator->__('Submit');
                break;
            case 'reject':
                $title = $this->translator->__('Reject');
                break;
            case 'approve':
                $title = $currentState == 'initial' ? $this->translator->__('Submit and approve') : $this->translator->__('Approve');
                break;
            case 'unpublish':
                $title = $this->translator->__('Unpublish');
                break;
            case 'publish':
                $title = $this->translator->__('Publish');
                break;
            case 'archive':
                $title = $this->translator->__('Archive');
                break;
            case 'unarchive':
                $title = $this->translator->__('Unarchive');
                break;
            case 'trash':
                $title = $this->translator->__('Trash');
                break;
            case 'recover':
                $title = $this->translator->__('Recover');
                break;
            case 'delete':
                $title = $this->translator->__('Delete');
                break;
        }
    
        if ($title == '') {
            if (substr($actionId, 0, 6) == 'update') {
                $title = $this->translator->__('Update');
            } elseif (substr($actionId, 0, 5) == 'trash') {
                $title = $this->translator->__('Trash');
            } elseif (substr($actionId, 0, 7) == 'recover') {
                $title = $this->translator->__('Recover');
        	}
        }
    
        return $title;
    }
    
    /**
     * Returns a button class for a certain action.
     *
     * @param string $actionId Id of the treated action
     *
     * @return string The button class
     */
    protected function getButtonClassForAction($actionId)
    {
        $buttonClass = '';
        switch ($actionId) {
            case 'defer':
                $buttonClass = '';
                break;
            case 'submit':
                $buttonClass = 'success';
                break;
            case 'reject':
                $buttonClass = '';
                break;
            case 'approve':
                $buttonClass = '';
                break;
            case 'unpublish':
                $buttonClass = '';
                break;
            case 'publish':
                $buttonClass = '';
                break;
            case 'archive':
                $buttonClass = '';
                break;
            case 'unarchive':
                $buttonClass = '';
                break;
            case 'trash':
                $buttonClass = '';
                break;
            case 'recover':
                $buttonClass = '';
                break;
            case 'delete':
                $buttonClass = 'danger';
                break;
        }
    
        if ($buttonClass == '' && substr($actionId, 0, 6) == 'update') {
            $buttonClass = 'success';
    	}
    
        if (empty($buttonClass)) {
            $buttonClass = 'default';
        }
    
        return 'btn btn-' . $buttonClass;
    }
    
    /**
     * Executes a certain workflow action for a given entity object.
     *
     * @param EntityAccess $entity    The given entity instance
     * @param string       $actionId  Name of action to be executed
     * @param boolean      $recursive True if the function called itself
     *
     * @return boolean Whether everything worked well or not
     */
    public function executeAction(EntityAccess $entity, $actionId = '', $recursive = false)
    {
        $workflow = $this->workflowRegistry->get($entity);
        if (!$workflow->can($entity, $actionId)) {
            return false;
        }
    
        // get entity manager
        $entityManager = $this->entityFactory->getObjectManager();
        $logArgs = ['app' => 'RKAlmanacModule', 'user' => $this->currentUserApi->get('uname')];
    
        $result = false;
    
        try {
            $workflow->apply($entity, $actionId);
    
            if ($actionId == 'delete') {
                $entityManager->remove($entity);
            } else {
                $entityManager->persist($entity);
            }
            $entityManager->flush();
    
            $result = true;
            if ($actionId == 'delete') {
                $this->logger->notice('{app}: User {user} deleted an entity.', $logArgs);
            } else {
                $this->logger->notice('{app}: User {user} updated an entity.', $logArgs);
            }
        } catch (\Exception $exception) {
            if ($actionId == 'delete') {
                $this->logger->error('{app}: User {user} tried to delete an entity, but failed.', $logArgs);
            } else {
                $this->logger->error('{app}: User {user} tried to update an entity, but failed.', $logArgs);
            }
            throw new \RuntimeException($exception->getMessage());
        }
    
        if (false !== $result && !$recursive) {
            $entities = $entity->getRelatedObjectsToPersist();
            foreach ($entities as $rel) {
                if ($rel->getWorkflowState() == 'initial') {
                    $this->executeAction($rel, $actionId, true);
                }
            }
        }
    
        return (false !== $result);
    }
    
    /**
     * Collects amount of moderation items foreach object type.
     *
     * @return array List of collected amounts
     */
    public function collectAmountOfModerationItems()
    {
        $amounts = [];
    
    
        // check if objects are waiting for approval
        $state = 'waiting';
        $objectType = 'date';
        if ($this->permissionApi->hasPermission('RKAlmanacModule:' . ucfirst($objectType) . ':', '::', ACCESS_ADD)) {
            $amount = $this->getAmountOfModerationItems($objectType, $state);
            if ($amount > 0) {
                $amounts[] = [
                    'aggregateType' => 'datesApproval',
                    'description' => $this->translator->__('Dates pending approval'),
                    'amount' => $amount,
                    'objectType' => $objectType,
                    'state' => $state,
                    'message' => $this->translator->_fn('One date is waiting for approval.', '%amount% dates are waiting for approval.', $amount, ['%amount%' => $amount])
                ];
        
                $this->logger->info('{app}: There are {amount} {entities} waiting for approval.', ['app' => 'RKAlmanacModule', 'amount' => $amount, 'entities' => 'dates']);
            }
        }
    
        return $amounts;
    }
    
    /**
     * Retrieves the amount of moderation items for a given object type
     * and a certain workflow state.
     *
     * @param string $objectType Name of treated object type
     * @param string $state The given state value
     *
     * @return integer The affected amount of objects
     */
    public function getAmountOfModerationItems($objectType, $state)
    {
        $repository = $this->entityFactory->getRepository($objectType);
    
        $where = 'tbl.workflowState = \'' . $state . '\'';
        $parameters = ['workflowState' => $state];
    
        return $repository->selectCount($where, false, $parameters);
    }
}
